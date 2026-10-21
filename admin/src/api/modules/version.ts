import http from "@/api";

/**
 * @name 版本管理模块
 */

// 版本接口返回类型
export interface VersionItem {
  id: number;
  version_code: string;
  version_number: number;
  platform: string;
  channel_code: string;
  package_name: string;
  download_url: string;
  update_log: string;
  force_update: number;
  status: number;
  sort: number;
  download_count?: number;
  created_at: string;
  updated_at: string;
}

// 版本列表响应类型
export interface VersionListResponse {
  total_num: number;
  list: VersionItem[];
}

// 版本添加/编辑参数类型
export interface VersionParams {
  id?: number;
  version_code: string;
  version_number?: number;
  platform: string;
  channel_code?: string;
  package_name?: string;
  download_url?: string;
  update_log?: string;
  force_update?: number;
  status?: number;
  sort?: number;
}

// 获取版本列表
export const getVersionListApi = (params?: {
  page?: number;
  limit?: number;
  version_code?: string;
  platform?: string;
  channel_code?: string;
  status?: number;
}) => {
  return http.get<VersionListResponse>(`/admin/version/list`, params, { loading: false });
};

// 获取指定版本信息
export const getVersionInfoApi = (id: number) => {
  return http.get<VersionItem>(`/admin/version/info`, { id }, { loading: false });
};

// 添加版本
export const addVersionApi = (params: VersionParams) => {
  return http.post(`/admin/version/add`, params);
};

// 编辑版本
export const updateVersionApi = (params: VersionParams) => {
  return http.post(`/admin/version/update`, params);
};

// 删除版本
export const deleteVersionApi = (params: { id: number }) => {
  return http.post(`/admin/version/del`, params);
};

// 批量删除版本
export const batchDeleteVersionApi = (params: { ids: number[] }) => {
  return http.post(`/admin/version/batchDelete`, params);
};

// 切换版本状态
export const changeVersionStatusApi = (params: { id: number; status: number }) => {
  return http.post(`/admin/version/changeStatus`, params);
};

