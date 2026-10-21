import http from "@/api";

/**
 * @name 菜单/权限管理模块
 */

// 菜单列表接口返回类型
export interface MenuItem {
  id: number;
  pid: number;
  sort: number;
  route: string;
  path: string;
  name: string;
  redirect: string;
  component: string;
  type: number; // 1=目录, 2=菜单, 3=按钮
  meta: {
    icon: string;
    title: string;
    isHide: boolean;
    isFull: boolean;
    isAffix: boolean;
  };
  children?: MenuItem[];
}

// 菜单添加/编辑参数类型
export interface MenuParams {
  id?: number;
  pid: number;
  title: string;
  sort: number;
  type: number; // 1=目录, 2=菜单, 3=按钮
  route?: string;
  path?: string;
  name?: string;
  redirect?: string;
  component?: string;
  icon?: string;
  is_hide?: boolean;
  is_full?: boolean;
  is_affix?: boolean;
}

// 获取菜单列表
export const getMenuListApi = () => {
  return http.get<MenuItem[]>(`/admin/permission/list`, {}, { loading: false });
};

// 获取指定菜单信息
export const getMenuInfoApi = (id: number) => {
  return http.get<MenuItem>(`/admin/permission/info`, { id }, { loading: false });
};

// 获取菜单下拉列表（用于选择父菜单）
export const getMenuSelectApi = () => {
  return http.get<any[]>(`/admin/permission/select`, {}, { loading: false });
};

// 添加菜单
export const addMenuApi = (params: MenuParams) => {
  return http.post(`/admin/permission/add`, params);
};

// 编辑菜单
export const updateMenuApi = (params: MenuParams) => {
  return http.post(`/admin/permission/update`, params);
};

// 删除菜单
export const deleteMenuApi = (params: { id: number }) => {
  return http.post(`/admin/permission/del`, params);
};

