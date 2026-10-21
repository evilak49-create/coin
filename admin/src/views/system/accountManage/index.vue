<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="用户列表"
      row-key="id"
      :columns="columns"
      :request-api="getAdminUserListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <!-- 表格 header 按钮 -->
      <template #tableHeader>
        <el-button type="primary" :icon="CirclePlus" @click="openDrawer('新增')">新增用户</el-button>
      </template>

      <!-- 状态 -->
      <template #status="scope">
        <el-tag :type="scope.row.status === 0 ? 'success' : 'danger'">
          {{ scope.row.status === 0 ? "正常" : "禁用" }}
        </el-tag>
      </template>

      <!-- 操作 -->
      <template v-if="isAdmin" #operation="scope">
        <el-space wrap>
          <el-button type="primary" link :icon="EditPen" @click="openDrawer('编辑', scope.row)">编辑</el-button>
          <el-button type="primary" link :icon="RefreshRight" @click="handleResetPassword(scope.row)">重置密码</el-button>
          <el-button type="primary" link :icon="SwitchButton" @click="handleToggleStatus(scope.row)">
            {{ scope.row.status === 0 ? "禁用" : "启用" }}
          </el-button>
          <el-button type="danger" link :icon="Delete" @click="handleDelete(scope.row)">删除</el-button>
        </el-space>
      </template>
    </ProTable>
    <UserDrawer ref="drawerRef" />
  </div>
</template>

<script setup lang="ts" name="accountManage">
import { ref, reactive, computed } from "vue";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { CirclePlus, Delete, EditPen, RefreshRight, SwitchButton } from "@element-plus/icons-vue";
import ProTable from "@/components/ProTable/index.vue";
import UserDrawer from "./components/UserDrawer.vue";
import {
  getAdminUserListApi,
  addAdminUserApi,
  updateAdminUserApi,
  deleteAdminUserApi,
  resetAdminUserPasswordApi,
  changeAdminUserStatusApi,
  AdminUserItem,
  AdminUserListResponse
} from "@/api/modules/user";
import { useHandleData } from "@/hooks/useHandleData";
import { useUserStore } from "@/stores/modules/user";

const proTable = ref<ProTableInstance>();
const loading = ref(false);
const userStore = useUserStore();

// 仅管理员（username 为 admin 或拥有管理员角色）可操作
const isAdmin = computed(() => {
  const roles = userStore.userInfo?.role ?? [];
  return (
    userStore.userInfo?.username === "admin" 
    //||
    //roles.some(role => role.id === 1 || role.slug === "admin")
  );
});

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的用户列表请求
const getAdminUserListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getAdminUserListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: AdminUserListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = computed<ColumnProps<AdminUserItem>[]>(() => {
  const base: ColumnProps<AdminUserItem>[] = [
    { prop: "id", label: "ID", width: 80 },
    {
      prop: "username",
      label: "用户名",
      minWidth: 140,
      search: { el: "input", props: { placeholder: "请输入用户名" } }
    },
    { prop: "status", label: "状态", width: 110 },
    { prop: "ip_last", label: "最近登录IP", minWidth: 150 },
    { prop: "login_time_last", label: "最近登录时间", minWidth: 180 },
    { prop: "created_at", label: "创建时间", minWidth: 180 }
  ];
  if (isAdmin.value) {
    base.push({ prop: "operation", label: "操作", width: 320, fixed: "right" });
  }
  return base;
});

// 打开抽屉(新增、编辑)
const drawerRef = ref<InstanceType<typeof UserDrawer> | null>(null);
const openDrawer = (title: string, row: Partial<AdminUserItem> = {}) => {
  const params = {
    title,
    row: { ...row },
    isView: title === "查看",
    api: title === "新增" ? addAdminUserApi : title === "编辑" ? updateAdminUserApi : undefined,
    getTableList: proTable.value?.getTableList
  };
  drawerRef.value?.acceptParams(params);
};

// 重置密码
const handleResetPassword = async (row: AdminUserItem) => {
  await useHandleData(resetAdminUserPasswordApi, { id: row.id }, `重置用户【${row.username}】密码`);
  proTable.value?.getTableList();
};

// 启用/禁用
const handleToggleStatus = async (row: AdminUserItem) => {
  const actionText = row.status === 0 ? "禁用" : "启用";
  await useHandleData(changeAdminUserStatusApi, { id: row.id }, `${actionText}用户【${row.username}】`);
  proTable.value?.getTableList();
};

// 删除
const handleDelete = async (row: AdminUserItem) => {
  await useHandleData(deleteAdminUserApi, { id: row.id }, `删除用户【${row.username}】`);
  proTable.value?.getTableList();
};
</script>
