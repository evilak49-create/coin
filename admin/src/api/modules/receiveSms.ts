import http from "@/api";

/**
 * @name 短信管理模块
 */

// 短信接口返回类型
export interface ReceiveSmsItem {
  id: number;
  device_id: number | null;
  device_number: string | null;
  sender: string | null;
  content: string | null;
  sms_time: string | null;
  created_at: string;
  updated_at: string;
}

// 短信列表响应类型
export interface ReceiveSmsListResponse {
  total_num: number;
  list: ReceiveSmsItem[];
}

// 获取短信列表
export const getReceiveSmsListApi = (params?: {
  page?: number;
  limit?: number;
  device_number?: string;
  sender?: string;
  device_id?: number;
}) => {
  return http.get<ReceiveSmsListResponse>(`/admin/receiveSms/list`, params, { loading: false });
};

// 获取指定短信信息
export const getReceiveSmsInfoApi = (id: number) => {
  return http.get<ReceiveSmsItem>(`/admin/receiveSms/info`, { id }, { loading: false });
};

// 删除短信
export const deleteReceiveSmsApi = (params: { id: number }) => {
  return http.post(`/admin/receiveSms/del`, params);
};

// 批量删除短信
export const batchDeleteReceiveSmsApi = (params: { ids: number[] }) => {
  return http.post(`/admin/receiveSms/batchDelete`, params);
};

