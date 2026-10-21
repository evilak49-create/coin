import http from "@/api";

export interface SystemConfigItem {
  id: number;
  key: string;
  value: string;
  group?: string | null;
  type?: "string" | "number" | "image" | string | null;
  description?: string | null;
}

export const getSystemConfigByGroupApi = (group: string) => {
  return http.get<SystemConfigItem[]>(`/admin/systemConfig/getByGroup`, { group }, { loading: false });
};

export const batchUpdateSystemConfigApi = (configs: { key: string; value: string | number | null }[]) => {
  return http.post(`/admin/systemConfig/batchUpdateValues`, { configs });
};

