import http from "@/api";

/**
 * @name 角色管理模块
 */

export interface RoleItem {
  id: number;
  name: string;
  slug: string;
}

// 角色列表（用于选择）
export const getRoleListApi = () => {
  return http.get<RoleItem[]>(`/admin/role/list`, {}, { loading: false });
};

