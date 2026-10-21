<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="IP白名单列表"
      row-key="id"
      :columns="columns"
      :request-api="getIpWhitelistListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <!-- 表格 header 按钮 -->
      <template #tableHeader>
        <el-button type="primary" :icon="CirclePlus" @click="openDrawer('新增')">新增IP白名单</el-button>
      </template>
      <!-- 状态 -->
      <template #status="scope">
        <el-tag :type="scope.row.status === 1 ? 'success' : 'info'">
          {{ scope.row.status === 1 ? '启用' : '禁用' }}
        </el-tag>
      </template>
      <!-- IP操作 -->
      <template #operation="scope">
        <el-button type="primary" link :icon="EditPen" @click="openDrawer('编辑', scope.row)">编辑</el-button>
        <el-button type="primary" link :icon="Delete" @click="deleteIpWhitelist(scope.row)">删除</el-button>
      </template>
    </ProTable>
    <IpWhitelistDrawer ref="drawerRef" />
  </div>
</template>

<script setup lang="ts" name="ipWhitelist">
import { ref, reactive } from "vue";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { Delete, EditPen, CirclePlus } from "@element-plus/icons-vue";
import ProTable from "@/components/ProTable/index.vue";
import IpWhitelistDrawer from "./components/IpWhitelistDrawer.vue";
import {
  getIpWhitelistListApi,
  addIpWhitelistApi,
  updateIpWhitelistApi,
  deleteIpWhitelistApi,
  IpWhitelistItem,
  IpWhitelistListResponse
} from "@/api/modules/ipWhitelist";
import { useHandleData } from "@/hooks/useHandleData";

const proTable = ref<ProTableInstance>();
const loading = ref(false);

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的IP白名单列表请求
const getIpWhitelistListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getIpWhitelistListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: IpWhitelistListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<IpWhitelistItem>[]>([
  { prop: "ip", label: "IP地址", width: 200, search: { el: "input", props: { placeholder: "请输入IP地址" } } },
  { prop: "description", label: "描述", width: 200, search: { el: "input", props: { placeholder: "请输入描述" } } },
  { prop: "status", label: "状态", width: 100 },
  { prop: "created_at", label: "创建时间", width: 180 },
  { prop: "updated_at", label: "更新时间", width: 180 },
  { prop: "operation", label: "操作", width: 200, fixed: "right" }
]);

// 删除IP白名单
const deleteIpWhitelist = async (row: IpWhitelistItem) => {
  await useHandleData(deleteIpWhitelistApi, { id: row.id }, `删除IP【${row.ip}】`);
  proTable.value?.getTableList();
};

// 打开 drawer(新增、编辑)
const drawerRef = ref<InstanceType<typeof IpWhitelistDrawer> | null>(null);
const openDrawer = (title: string, row: Partial<IpWhitelistItem> = {}) => {
  const params = {
    title,
    row: { ...row },
    isView: title === "查看",
    api: title === "新增" ? addIpWhitelistApi : title === "编辑" ? updateIpWhitelistApi : undefined,
    getTableList: proTable.value?.getTableList
  };
  drawerRef.value?.acceptParams(params);
};
</script>

