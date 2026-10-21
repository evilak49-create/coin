import http from "@/api";

/**
 * @name IP白名单管理模块
 */

// IP白名单接口返回类型
export interface IpWhitelistItem {
  id: number;
  ip: string;
  type: string; // whitelist-白名单, blacklist-黑名单
  description: string;
  status: number; // 0-禁用, 1-启用
  created_at: string;
  updated_at: string;
}

// IP白名单列表响应类型
export interface IpWhitelistListResponse {
  total_num: number;
  list: IpWhitelistItem[];
}

// IP白名单添加/编辑参数类型
export interface IpWhitelistParams {
  id?: number;
  ip: string;
  type?: string; // whitelist-白名单, blacklist-黑名单
  description?: string;
  status?: number; // 0-禁用, 1-启用
}

// 获取IP白名单列表
export const getIpWhitelistListApi = (params?: {
  page?: number;
  limit?: number;
  ip?: string;
  type?: string;
  status?: number;
}) => {
  return http.get<IpWhitelistListResponse>(`/admin/ipWhitelist/list`, params, { loading: false });
};

// 获取指定IP白名单信息
export const getIpWhitelistInfoApi = (id: number) => {
  return http.get<IpWhitelistItem>(`/admin/ipWhitelist/info`, { id }, { loading: false });
};

// 添加IP白名单
export const addIpWhitelistApi = (params: IpWhitelistParams) => {
  return http.post(`/admin/ipWhitelist/add`, params);
};

// 编辑IP白名单
export const updateIpWhitelistApi = (params: IpWhitelistParams) => {
  return http.post(`/admin/ipWhitelist/update`, params);
};

// 删除IP白名单
export const deleteIpWhitelistApi = (params: { id: number }) => {
  return http.post(`/admin/ipWhitelist/del`, params);
};

// IP白名单检查响应类型
export interface IpWhitelistCheckResponse {
  ip: string;
  result: 'pass' | 'fail';
}

// 检查IP是否在白名单中（服务端自动获取客户端IP）
export const checkIpWhitelistApi = () => {
  return http.get<IpWhitelistCheckResponse>(`/admin/ipWhitelist/check`, {}, { loading: false });
};

