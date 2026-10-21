import http from "@/api";
import { User } from "@/api/interface";

/**
 * @name 管理员账号管理模块
 */

// 单个管理员信息
export interface AdminUserItem extends User.ResUserList {
  role_ids?: number[];
}

// 管理员列表响应
export interface AdminUserListResponse {
  total_num: number;
  list: AdminUserItem[];
}

// 新增/编辑参数
export interface AdminUserParams {
  id?: number;
  username: string;
  status: number;
  role_ids: number[];
}

// 获取管理员列表
export const getAdminUserListApi = (params?: { page?: number; limit?: number; username?: string }) => {
  return http.get<AdminUserListResponse>(`/admin/user/list`, params, { loading: false });
};

// 获取指定管理员
export const getAdminUserInfoApi = (id: number) => {
  return http.get<AdminUserItem>(`/admin/user/info`, { id }, { loading: false });
};

// 添加管理员
export const addAdminUserApi = (params: AdminUserParams) => {
  const payload = { ...params };
  delete (payload as any).id;
  return http.post(`/admin/user/add`, payload);
};

// 编辑管理员
export const updateAdminUserApi = (params: AdminUserParams) => {
  return http.post(`/admin/user/update`, params);
};

// 删除管理员
export const deleteAdminUserApi = (params: { id: number }) => {
  return http.post(`/admin/user/del`, params);
};

// 重置密码
export const resetAdminUserPasswordApi = (params: { id: number }) => {
  return http.post(`/admin/user/resetPassword`, params);
};

// 账号禁用/启用
export const changeAdminUserStatusApi = (params: { id: number }) => {
  return http.post(`/admin/user/changeStatus`, params);
};

// 获取部门树（前端模拟数据，无后端接口）
export const getUserDepartment = () => {
  return Promise.resolve({
    data: [
      {
        id: "1",
        name: "总部",
        children: [
          {
            id: "11",
            name: "产品部",
            children: [
              { id: "111", name: "产品一部" },
              { id: "112", name: "产品二部" }
            ]
          },
          {
            id: "12",
            name: "技术部",
            children: [
              { id: "121", name: "前端组" },
              { id: "122", name: "后端组" },
              { id: "123", name: "测试组" }
            ]
          },
          { id: "13", name: "运营部" }
        ]
      },
      {
        id: "2",
        name: "分部A",
        children: [
          { id: "21", name: "市场部" },
          { id: "22", name: "客服部" }
        ]
      }
    ]
  });
};

// 导出用户信息模板（用于批量导入）
export const exportUserInfo = (params?: any) => {
  return http.download(`/admin/user/export`, params, { loading: false });
};

// 批量添加用户
export const BatchAddUser = (params: FormData) => {
  return http.post(`/admin/user/batchAdd`, params, { loading: false });
};

// 获取用户性别枚举（前端模拟数据，无后端接口）
export const getUserGender = () => {
  return Promise.resolve({
    data: [
      { genderLabel: "男", genderValue: 1 },
      { genderLabel: "女", genderValue: 2 }
    ]
  });
};

// 获取用户状态枚举（前端模拟数据，无后端接口）
export const getUserStatus = () => {
  return Promise.resolve({
    data: [
      { userLabel: "启用", userStatus: 1 },
      { userLabel: "禁用", userStatus: 0 }
    ]
  });
};

// 以下函数为示例代码使用，实际项目中请根据后端接口实现
// 获取用户列表（示例代码用，无后端接口）
export const getUserList = (params?: any) => {
  return Promise.resolve({
    list: [],
    total: 0
  });
};

// 删除用户（示例代码用，无后端接口）
export const deleteUser = (params: { id: number | number[] }) => {
  return Promise.resolve({ code: 0, msg: "删除成功" });
};

// 编辑用户（示例代码用，无后端接口）
export const editUser = (params: any) => {
  return Promise.resolve({ code: 0, msg: "编辑成功" });
};

// 添加用户（示例代码用，无后端接口）
export const addUser = (params: any) => {
  return Promise.resolve({ code: 0, msg: "添加成功" });
};

// 重置用户密码（示例代码用，无后端接口）
export const resetUserPassWord = (params: { id: number }) => {
  return Promise.resolve({ code: 0, msg: "重置密码成功" });
};

// 改变用户状态（示例代码用，无后端接口）
export const changeUserStatus = (params: { id: number; status: number }) => {
  return Promise.resolve({ code: 0, msg: "状态更新成功" });
};

// 获取用户角色枚举（示例代码用，无后端接口）
export const getUserRole = () => {
  return Promise.resolve({
    data: [
      { roleLabel: "管理员", roleValue: 1 },
      { roleLabel: "普通用户", roleValue: 2 }
    ]
  });
};

// 获取用户树形列表（示例代码用，无后端接口）
export const getUserTreeList = (params?: any) => {
  return Promise.resolve({
    list: [
      {
        id: 1,
        username: "管理员",
        gender: 1,
        idCard: "110101199001011234",
        email: "admin@example.com",
        address: "北京市",
        status: 1,
        createTime: "2024-01-01 10:00:00",
        children: [
          {
            id: 11,
            username: "子用户1",
            gender: 1,
            idCard: "110101199001011235",
            email: "user1@example.com",
            address: "北京市",
            status: 1,
            createTime: "2024-01-02 10:00:00"
          },
          {
            id: 12,
            username: "子用户2",
            gender: 2,
            idCard: "110101199001011236",
            email: "user2@example.com",
            address: "北京市",
            status: 1,
            createTime: "2024-01-03 10:00:00"
          }
        ]
      }
    ],
    total: 1
  });
};
