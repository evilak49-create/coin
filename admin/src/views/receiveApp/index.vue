<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="应用列表"
      row-key="id"
      :columns="columns"
      :request-api="getReceiveAppListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <!-- 表格 header 按钮 -->
      <template #tableHeader="scope">
        <el-button type="danger" :icon="Delete" plain :disabled="!scope.isSelected" @click="batchDelete(scope.selectedListIds)">
          批量删除
        </el-button>
      </template>
      <!-- 应用名称 -->
      <template #app_name="scope">
        <el-tooltip :content="scope.row.app_name" placement="top" :disabled="!scope.row.app_name">
          <div class="app-name-text">{{ scope.row.app_name || "-" }}</div>
        </el-tooltip>
      </template>
      <!-- 应用包名 -->
      <template #app_package="scope">
        <el-tooltip :content="scope.row.app_package" placement="top" :disabled="!scope.row.app_package">
          <div class="app-package-text">{{ scope.row.app_package || "-" }}</div>
        </el-tooltip>
      </template>
      <!-- 操作 -->
      <template #operation="scope">
        <el-button type="primary" link :icon="View" @click="viewDetail(scope.row)">查看</el-button>
        <el-button type="danger" link :icon="Delete" @click="deleteApp(scope.row)">删除</el-button>
      </template>
    </ProTable>
    <!-- 详情对话框 -->
    <el-dialog v-model="dialogVisible" title="应用详情" width="600px">
      <el-descriptions :column="1" border v-if="detailData">
        <el-descriptions-item label="ID">{{ detailData.id }}</el-descriptions-item>
        <el-descriptions-item label="设备ID">{{ detailData.device_id || "-" }}</el-descriptions-item>
        <el-descriptions-item label="设备号">{{ detailData.device_number || "-" }}</el-descriptions-item>
        <el-descriptions-item label="应用名称">{{ detailData.app_name || "-" }}</el-descriptions-item>
        <el-descriptions-item label="应用包名">
          <div style="word-break: break-all;">{{ detailData.app_package || "-" }}</div>
        </el-descriptions-item>
        <el-descriptions-item label="安装时间">{{ detailData.install_time || "-" }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detailData.created_at || "-" }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ detailData.updated_at || "-" }}</el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <el-button @click="dialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts" name="receiveApp">
import { ref, reactive } from "vue";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { Delete, View } from "@element-plus/icons-vue";
import ProTable from "@/components/ProTable/index.vue";
import {
  getReceiveAppListApi,
  getReceiveAppInfoApi,
  deleteReceiveAppApi,
  batchDeleteReceiveAppApi,
  ReceiveAppItem,
  ReceiveAppListResponse
} from "@/api/modules/receiveApp";
import { useHandleData } from "@/hooks/useHandleData";
import { ElMessage } from "element-plus";

const proTable = ref<ProTableInstance>();
const loading = ref(false);
const dialogVisible = ref(false);
const detailData = ref<ReceiveAppItem | null>(null);

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的应用列表请求
const getReceiveAppListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getReceiveAppListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: ReceiveAppListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<ReceiveAppItem>[]>([
  { type: "selection", fixed: "left", width: 60 },
  { prop: "id", label: "ID", width: 80 },
  { 
    prop: "device_number", 
    label: "设备号", 
    width: 150, 
    search: { el: "input", props: { placeholder: "请输入设备号" } } 
  },
  { 
    prop: "app_name", 
    label: "应用名称", 
    width: 200, 
    search: { el: "input", props: { placeholder: "请输入应用名称" } },
    showOverflowTooltip: false
  },
  { 
    prop: "app_package", 
    label: "应用包名", 
    minWidth: 250,
    search: { el: "input", props: { placeholder: "请输入应用包名" } },
    showOverflowTooltip: false
  },
  { 
    prop: "install_time", 
    label: "安装时间", 
    width: 180,
    search: { el: "input", props: { placeholder: "请输入安装时间" } }
  },
  { prop: "created_at", label: "创建时间", width: 180 },
  { prop: "operation", label: "操作", width: 200, fixed: "right" }
]);

// 查看详情
const viewDetail = async (row: ReceiveAppItem) => {
  try {
    const result = await getReceiveAppInfoApi(row.id);
    detailData.value = result.data;
    dialogVisible.value = true;
  } catch (error) {
    ElMessage.error("获取应用详情失败");
  }
};

// 删除应用
const deleteApp = async (row: ReceiveAppItem) => {
  await useHandleData(deleteReceiveAppApi, { id: row.id }, `删除应用`);
  proTable.value?.getTableList();
};

// 批量删除
const batchDelete = async (selectedListIds: number[]) => {
  if (!selectedListIds.length) {
    ElMessage.warning("请选择要删除的应用");
    return;
  }
  
  await useHandleData(batchDeleteReceiveAppApi, { ids: selectedListIds }, `批量删除 ${selectedListIds.length} 个应用`);
  proTable.value?.getTableList();
  proTable.value?.clearSelection();
};
</script>

<style scoped lang="scss">
.app-name-text,
.app-package-text {
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-break: break-all;
}
</style>

