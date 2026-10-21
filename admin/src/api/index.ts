import axios, { AxiosInstance, AxiosError, AxiosRequestConfig, InternalAxiosRequestConfig, AxiosResponse } from "axios";
import { showFullScreenLoading, tryHideFullScreenLoading } from "@/components/Loading/fullScreen";
import { LOGIN_URL } from "@/config";
import { ElMessage } from "element-plus";
import { ResultData } from "@/api/interface";
import { ResultEnum } from "@/enums/httpEnum";
import { checkStatus } from "./helper/checkStatus";
import { AxiosCanceler } from "./helper/axiosCancel";
import { useUserStore } from "@/stores/modules/user";
import router from "@/routers";

export interface CustomAxiosRequestConfig extends InternalAxiosRequestConfig {
  loading?: boolean;
  cancel?: boolean;
  _retry?: boolean; // 标记是否已尝试刷新 token
}

const config = {
  // 默认地址请求地址，可在 .env.** 文件中修改
  baseURL: import.meta.env.VITE_API_URL as string,
  // 设置超时时间
  timeout: ResultEnum.TIMEOUT as number,
  // 跨域时候允许携带凭证
  withCredentials: false
};

const axiosCanceler = new AxiosCanceler();

class RequestHttp {
  service: AxiosInstance;
  public constructor(config: AxiosRequestConfig) {
    // instantiation
    this.service = axios.create(config);

    /**
     * @description 请求拦截器
     * 客户端发送请求 -> [请求拦截器] -> 服务器
     * token校验(JWT) : 接受服务器返回的 token,存储到 vuex/pinia/本地储存当中
     */
    this.service.interceptors.request.use(
      (config: CustomAxiosRequestConfig) => {
        const userStore = useUserStore();
        // 重复请求不需要取消，在 api 服务中通过指定的第三个参数: { cancel: false } 来控制
        config.cancel ??= true;
        config.cancel && axiosCanceler.addPending(config);
        // 当前请求不需要显示 loading，在 api 服务中通过指定的第三个参数: { loading: false } 来控制
        config.loading ??= true;
        config.loading && showFullScreenLoading();
        // JWT token 使用 Authorization Bearer 格式
        if (userStore.token) {
          config.headers = config.headers || {};
          config.headers.Authorization = `Bearer ${userStore.token}`;
        }
        return config;
      },
      (error: AxiosError) => {
        return Promise.reject(error);
      }
    );

    /**
     * @description 响应拦截器
     *  服务器换返回信息 -> [拦截统一处理] -> 客户端JS获取到信息
     */
    this.service.interceptors.response.use(
      (response: AxiosResponse & { config: CustomAxiosRequestConfig }) => {
        const { data, config } = response;

        const userStore = useUserStore();
        axiosCanceler.removePending(config);
        config.loading && tryHideFullScreenLoading();
        // 登录失效 - 尝试刷新 token
        if (data.code == ResultEnum.OVERDUE) {
          // 如果已经尝试过刷新，直接跳转登录
          if (config._retry) {
            userStore.setToken("");
            sessionStorage.removeItem('ip_whitelist_checked');
            router.replace(LOGIN_URL);
            ElMessage.error(data.msg);
            return Promise.reject(data);
          }
          // 尝试刷新 token
          return this.handleTokenRefresh(config);
        }
        // 后端成功返回 code=0，前端成功返回 code=200
        // 全局错误信息拦截（防止下载文件的时候返回数据流，没有 code 直接报错）
        if (data.code !== undefined && data.code !== 0 && data.code !== ResultEnum.SUCCESS) {
          ElMessage.error(data.msg);
          return Promise.reject(data);
        }
        // 成功请求（在页面上除非特殊情况，否则不用处理失败逻辑）
        return data;
      },
      async (error: AxiosError) => {
        const { response } = error;
        tryHideFullScreenLoading();
        // 请求超时 && 网络错误单独判断，没有 response
        if (error.message.indexOf("timeout") !== -1) ElMessage.error("请求超时！请您稍后重试");
        if (error.message.indexOf("Network Error") !== -1) ElMessage.error("网络错误！请您稍后重试");
        // 根据服务器响应的错误状态码，做不同的处理
        if (response) checkStatus(response.status);
        // 服务器结果都没有返回(可能服务器错误可能客户端断网)，断网处理:可以跳转到断网页面
        if (!window.navigator.onLine) router.replace("/500");
        return Promise.reject(error);
      }
    );
  }

  // 正在刷新 token 的标志
  private isRefreshing = false;
  // 等待刷新 token 的请求队列
  private refreshSubscribers: Array<(token: string) => void> = [];

  /**
   * @description 处理 token 刷新
   */
  private async handleTokenRefresh(config: CustomAxiosRequestConfig): Promise<any> {
    const userStore = useUserStore();

    if (!this.isRefreshing) {
      this.isRefreshing = true;
      try {
        // 调用刷新 token 接口
        const response = await axios.put(
          `${import.meta.env.VITE_API_URL}/admin/auth/refresh`,
          {},
          {
            headers: { Authorization: `Bearer ${userStore.token}` }
          }
        );
        
        if (response.data.code === 0 && response.data.data?.access_token) {
          const newToken = response.data.data.access_token;
          userStore.setToken(newToken);
          
          // 通知所有等待的请求使用新 token
          this.refreshSubscribers.forEach(callback => callback(newToken));
          this.refreshSubscribers = [];
          
          // 重试原始请求
          config._retry = true;
          config.headers.Authorization = `Bearer ${newToken}`;
          return this.service(config);
        } else {
          // 刷新失败，跳转登录
          userStore.setToken("");
          sessionStorage.removeItem('ip_whitelist_checked');
          router.replace(LOGIN_URL);
          ElMessage.error("登录已过期，请重新登录");
          return Promise.reject(response.data);
        }
      } catch (error) {
        // 刷新失败，跳转登录
        userStore.setToken("");
        sessionStorage.removeItem('ip_whitelist_checked');
        router.replace(LOGIN_URL);
        ElMessage.error("登录已过期，请重新登录");
        return Promise.reject(error);
      } finally {
        this.isRefreshing = false;
      }
    } else {
      // 如果正在刷新，将请求加入等待队列
      return new Promise(resolve => {
        this.refreshSubscribers.push((token: string) => {
          config._retry = true;
          config.headers.Authorization = `Bearer ${token}`;
          resolve(this.service(config));
        });
      });
    }
  }

  /**
   * @description 常用请求方法封装
   */
  get<T>(url: string, params?: object, _object = {}): Promise<ResultData<T>> {
    return this.service.get(url, { params, ..._object });
  }
  post<T>(url: string, params?: object | string, _object = {}): Promise<ResultData<T>> {
    return this.service.post(url, params, _object);
  }
  put<T>(url: string, params?: object, _object = {}): Promise<ResultData<T>> {
    return this.service.put(url, params, _object);
  }
  delete<T>(url: string, params?: any, _object = {}): Promise<ResultData<T>> {
    return this.service.delete(url, { params, ..._object });
  }
  download(url: string, params?: object, _object = {}): Promise<BlobPart> {
    return this.service.post(url, params, { ..._object, responseType: "blob" });
  }
}

export default new RequestHttp(config);
