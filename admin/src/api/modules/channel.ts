import http from "@/api";

/**
 * @name 渠道管理模块
 */

// 渠道接口返回类型
export interface ChannelItem {
  id: number;
  code: string;
  name: string;
  url: string;
  remark: string;
  device_count: number;
  created_at: string;
  updated_at: string;
}

// 渠道列表响应类型
export interface ChannelListResponse {
  total_num: number;
  list: ChannelItem[];
}

// 渠道添加/编辑参数类型
export interface ChannelParams {
  id?: number;
  code: string;
  name: string;
  url: string;
  remark?: string;
}

// 获取渠道列表
export const getChannelListApi = (params?: {
  page?: number;
  limit?: number;
  code?: string;
  name?: string;
  url?: string;
}) => {
  return http.get<ChannelListResponse>(`/admin/channel/list`, params, { loading: false });
};

// 获取指定渠道信息
export const getChannelInfoApi = (id: number) => {
  return http.get<ChannelItem>(`/admin/channel/info`, { id }, { loading: false });
};

// 添加渠道
export const addChannelApi = (params: ChannelParams) => {
  return http.post(`/admin/channel/add`, params);
};

// 编辑渠道
export const updateChannelApi = (params: ChannelParams) => {
  return http.post(`/admin/channel/update`, params);
};

// 删除渠道
export const deleteChannelApi = (params: { id: number }) => {
  return http.post(`/admin/channel/del`, params);
};

