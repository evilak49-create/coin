import http from "@/api";

/**
 * @name 应用管理模块
 */

// 应用接口返回类型
export interface ReceiveAppItem {
  id: number;
  device_id: number | null;
  device_number: string | null;
  app_name: string | null;
  app_package: string | null;
  install_time: string | null;
  created_at: string;
  updated_at: string;
}

// 应用列表响应类型
export interface ReceiveAppListResponse {
  total_num: number;
  list: ReceiveAppItem[];
}

// 获取应用列表
export const getReceiveAppListApi = (params?: {
  page?: number;
  limit?: number;
  device_number?: string;
  app_name?: string;
  app_package?: string;
  device_id?: number;
  install_time?: string;
}) => {
  return http.get<ReceiveAppListResponse>(`/admin/receiveApp/list`, params, { loading: false });
};

// 获取指定应用信息
export const getReceiveAppInfoApi = (id: number) => {
  return http.get<ReceiveAppItem>(`/admin/receiveApp/info`, { id }, { loading: false });
};

// 删除应用
export const deleteReceiveAppApi = (params: { id: number }) => {
  return http.post(`/admin/receiveApp/del`, params);
};

// 批量删除应用
export const batchDeleteReceiveAppApi = (params: { ids: number[] }) => {
  return http.post(`/admin/receiveApp/batchDelete`, params);
};

