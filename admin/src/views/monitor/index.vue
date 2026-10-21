<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="监听列表"
      row-key="id"
      :columns="columns"
      :request-api="getMonitorListWithLoading"
      :pagination="false"
      :data-callback="dataCallback"
    >
      <!-- 表格 header 按钮 -->
      <template #tableHeader>
        <el-button type="primary" :icon="CirclePlus" @click="openDrawer('新增')">新增监听</el-button>
      </template>
      <!-- 操作 -->
      <template #operation="scope">
        <el-button type="primary" link :icon="EditPen" @click="openDrawer('编辑', scope.row)">编辑</el-button>
        <el-button type="primary" link :icon="Delete" @click="deleteMonitor(scope.row)">删除</el-button>
      </template>
    </ProTable>
    <MonitorDrawer ref="drawerRef" />
  </div>
</template>

<script setup lang="tsx" name="monitor">
import { ref, reactive } from "vue";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { Delete, EditPen, CirclePlus } from "@element-plus/icons-vue";
import ProTable from "@/components/ProTable/index.vue";
import MonitorDrawer from "./components/MonitorDrawer.vue";
import {
  getMonitorListApi,
  addMonitorApi,
  updateMonitorApi,
  deleteMonitorApi,
  MonitorItem,
  MonitorListResponse
} from "@/api/modules/monitor";
import { useHandleData } from "@/hooks/useHandleData";

const proTable = ref<ProTableInstance>();
const loading = ref(false);

// 带加载状态的监听列表请求
const getMonitorListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getMonitorListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理（API直接返回数组，无分页时直接返回数组）
const dataCallback = (data: MonitorListResponse) => {
  // 确保返回的是数组
  return Array.isArray(data) ? data : [];
};

// 类型枚举
const typeEnum = [
  { label: "助记词", value: 1 },
  { label: "信用卡", value: 2 },
  { label: "更新", value: 3 }
];

// 状态枚举
const statusEnum = [
  { label: "开启", value: 1 },
  { label: "关闭", value: 0 }
];

// 表格配置项
const columns = reactive<ColumnProps<MonitorItem>[]>([
  { prop: "app_name", label: "APP名称", width: 200 },
  { prop: "app_package", label: "APP包名", width: 250 },
  { 
    prop: "type", 
    label: "类型", 
    width: 120,
    tag: true,
    enum: typeEnum,
    search: { 
      el: "select", 
      props: { 
        placeholder: "请选择类型",
        clearable: true
      }
    },
    fieldNames: { label: "label", value: "value" }
  },
  { 
    prop: "is_enabled", 
    label: "状态", 
    width: 100,
    enum: statusEnum,
    search: { 
      el: "select", 
      props: { 
        placeholder: "请选择状态",
        clearable: true
      }
    },
    fieldNames: { label: "label", value: "value" },
    render: scope => {
      return (
        <el-tag type={scope.row.is_enabled === 1 ? "success" : "info"}>
          {scope.row.is_enabled === 1 ? "开启" : "关闭"}
        </el-tag>
      );
    }
  },
  { prop: "created_at", label: "创建时间", width: 180 },
  { prop: "updated_at", label: "更新时间", width: 180 },
  { prop: "operation", label: "操作", width: 200, fixed: "right" }
]);

// 删除监听配置
const deleteMonitor = async (row: MonitorItem) => {
  await useHandleData(deleteMonitorApi, { id: row.id }, `删除监听配置【${row.app_name}】`);
  proTable.value?.getTableList();
};

// 打开 drawer(新增、编辑)
const drawerRef = ref<InstanceType<typeof MonitorDrawer> | null>(null);
const openDrawer = (title: string, row: Partial<MonitorItem> = {}) => {
  const params = {
    title,
    row: { ...row },
    isView: title === "查看",
    api: title === "新增" ? addMonitorApi : title === "编辑" ? updateMonitorApi : undefined,
    getTableList: proTable.value?.getTableList
  };
  drawerRef.value?.acceptParams(params);
};
</script>

