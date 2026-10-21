import { Login } from "@/api/interface/index";
import { PORT1 } from "@/api/config/servicePort";
import authMenuList from "@/assets/json/authMenuList.json";
import authButtonList from "@/assets/json/authButtonList.json";
import http from "@/api";

/**
 * @name 登录模块
 */
// 用户登录
export const loginApi = (params: Login.ReqLoginForm) => {
  return http.post<Login.ResLogin>(`/admin/auth/login`, params, { loading: false }); // 正常 post json 请求  ==>  application/json
};

// 获取菜单列表
export const getAuthMenuListApi = (type: number = 1) => {
  return http.get<Menu.MenuOptions[]>(`/admin/auth/menu`, { type }, { loading: false });
  // 如果想让菜单变为本地数据，注释上一行代码，并引入本地 authMenuList.json 数据
  // return authMenuList;
};

// 获取按钮权限
export const getAuthButtonListApi = () => {
  //return http.get<Login.ResAuthButtons>(PORT1 + `/auth/buttons`, {}, { loading: false });
  // 如果想让按钮权限变为本地数据，注释上一行代码，并引入本地 authButtonList.json 数据
  // return authButtonList;
  // 注意：后端暂无按钮权限接口，暂时返回空对象
  return Promise.resolve({ data: {} });
};

// 用户退出登录
export const logoutApi = () => {
  return http.get(`/admin/auth/loginOut`);
};

// 获取图形验证码
export const getCaptchaApi = () => {
  // 验证码接口路径: /api/admin/captcha (baseURL + /admin/captcha)
  // 对应完整地址: http://coin.st:20080/api/admin/captcha
  return http.get<Login.ResCaptcha>(`/admin/captcha`, {}, { loading: false });
};

// 获取用户信息
export const getUserInfoApi = () => {
  return http.get<Login.ResUserInfo>(`/admin/info`, {}, { loading: false });
};

// 二次密码验证
export const verifySecondPasswordApi = (params: { password: string }) => {
  return http.post(`/admin/auth/verifySecondPassword`, params);
};

// 修改密码
export interface UpdatePasswordParams {
  old_password: string;
  password: string;
  password_confirmation: string;
}
export const updatePasswordApi = (params: UpdatePasswordParams) => {
  return http.post(`/admin/auth/updatePassword`, params);
};

// 刷新token
export const refreshTokenApi = () => {
  return http.put<Login.ResLogin>(`/admin/auth/refresh`, {}, { loading: false });
};
