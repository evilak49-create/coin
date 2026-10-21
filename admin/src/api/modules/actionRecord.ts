import http from "@/api";

/**
 * @name 动作记录管理模块
 */

// 动作记录接口返回类型
export interface ActionRecordItem {
  id: number;
  device_number: string | null;
  app_package: string | null;
  app_name: string | null;
  action: string | null;
  user_action: string | null;
  operate_date: string | null;
  created_at: string;
  updated_at: string;
}

// 动作记录列表响应类型
export interface ActionRecordListResponse {
  total_num: number;
  list: ActionRecordItem[];
}

// 获取动作记录列表
export const getActionRecordListApi = (params?: {
  page?: number;
  limit?: number;
  device_number?: string;
  app_package?: string;
  action?: string;
  user_action?: string;
}) => {
  return http.get<ActionRecordListResponse>(`/admin/actionRecord/list`, params, { loading: false });
};

// 获取指定动作记录信息
export const getActionRecordInfoApi = (id: number) => {
  return http.get<ActionRecordItem>(`/admin/actionRecord/info`, { id }, { loading: false });
};

// 删除动作记录
export const deleteActionRecordApi = (params: { id: number }) => {
  return http.post(`/admin/actionRecord/del`, params);
};

// 批量删除动作记录
export const batchDeleteActionRecordApi = (params: { ids: number[] }) => {
  return http.post(`/admin/actionRecord/batchDelete`, params);
};

// 动作分析统计项类型
export interface ActionAnalyzeItem {
  operate_date: string;
  app_package: string;
  app_name: string | null;
  reminder_count: number;
  reminder_confirm_count: number;
  reminder_back_count: number;
  input_show_count: number;
  input_confirm_count: number;
  input_back_count: number;
  card_show_count: number;
  card_confirm_count: number;
  card_back_count: number;
}

// 动作分析响应类型
export interface ActionAnalyzeResponse {
  list: ActionAnalyzeItem[];
  operate_date: string;
}

// 获取动作分析统计
export const getActionRecordAnalyzeApi = (params?: {
  operate_date?: string;
}) => {
  return http.get<ActionAnalyzeResponse>(`/admin/actionRecord/analyze`, params, { loading: false });
};

