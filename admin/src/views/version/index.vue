<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="版本列表"
      row-key="id"
      :columns="columns"
      :request-api="getVersionListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <!-- 表格 header 按钮 -->
      <template #tableHeader>
        <el-button type="primary" :icon="CirclePlus" @click="openDrawer('新增')">新增版本</el-button>
      </template>
      <!-- 平台 -->
      <template #platform="scope">
        <el-tag :type="scope.row.platform === 'android' ? 'success' : 'info'">
          {{ scope.row.platform === 'android' ? 'Android' : 'iOS' }}
        </el-tag>
      </template>
      <!-- 下载地址 -->
      <template #download_url="scope">
        <div class="download-url-cell">{{ scope.row.download_url || '-' }}</div>
      </template>
      <!-- 强制更新 -->
      <template #force_update="scope">
        <el-tag :type="scope.row.force_update === 1 ? 'danger' : 'info'">
          {{ scope.row.force_update === 1 ? '是' : '否' }}
        </el-tag>
      </template>
      <!-- 状态 -->
      <template #status="scope">
        <el-switch
          v-model="scope.row.status"
          :active-value="1"
          :inactive-value="0"
          @change="(val: string | number | boolean) => {
            const statusValue = typeof val === 'boolean' ? (val ? 1 : 0) : (typeof val === 'string' ? Number(val) : val);
            handleStatusChange(scope.row, statusValue);
          }"
        />
      </template>
      <!-- 操作 -->
      <template #operation="scope">
        <el-button type="primary" link :icon="EditPen" @click="openDrawer('编辑', scope.row)">编辑</el-button>
        <el-button type="primary" link :icon="Delete" @click="deleteVersion(scope.row)">删除</el-button>
      </template>
    </ProTable>
    <VersionDrawer ref="drawerRef" />
  </div>
</template>

<script setup lang="ts" name="version">
import { ref, reactive } from "vue";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { Delete, EditPen, CirclePlus } from "@element-plus/icons-vue";
import ProTable from "@/components/ProTable/index.vue";
import VersionDrawer from "./components/VersionDrawer.vue";
import {
  getVersionListApi,
  addVersionApi,
  updateVersionApi,
  deleteVersionApi,
  changeVersionStatusApi,
  VersionItem,
  VersionListResponse
} from "@/api/modules/version";
import { useHandleData } from "@/hooks/useHandleData";

const proTable = ref<ProTableInstance>();
const loading = ref(false);

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的版本列表请求
const getVersionListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getVersionListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: VersionListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<VersionItem>[]>([
  {
    prop: "version_code",
    label: "版本号",
    width: 100,
    search: { el: "input", props: { placeholder: "请输入版本号" } }
  },
  {
    prop: "version_number",
    label: "版本数字",
    width: 90
  },
  {
    prop: "platform",
    label: "平台",
    width: 100
  },
  {
    prop: "channel_code",
    label: "渠道编号",
    width: 100,
    search: { el: "input", props: { placeholder: "请输入渠道编号" } }
  },
  {
    prop: "download_count",
    label: "下载次数",
    width: 90
  },
  {
    prop: "package_name",
    label: "包名",
    width: 200,
    search: { el: "input", props: { placeholder: "请输入包名" } }
  },
  {
    prop: "download_url",
    label: "下载地址",
    width: 300,
    showOverflowTooltip: false
  },
  {
    prop: "force_update",
    label: "强制更新",
    width: 120
  },
  {
    prop: "status",
    label: "状态",
    width: 90
  },
  {
    prop: "sort",
    label: "排序",
    width: 90
  },
  {
    prop: "created_at",
    label: "创建时间",
    width: 180
  },
  {
    prop: "updated_at",
    label: "更新时间",
    width: 180
  },
  {
    prop: "operation",
    label: "操作",
    width: 200,
    fixed: "right"
  }
]);

// 删除版本
const deleteVersion = async (row: VersionItem) => {
  await useHandleData(deleteVersionApi, { id: row.id }, `删除版本【${row.version_code}】`);
  proTable.value?.getTableList();
};

// 切换状态
const handleStatusChange = async (row: VersionItem, status?: number) => {
  // 确保 row 对象存在且有 id
  if (!row) {
    console.error('状态切换失败：row 对象为空');
    return;
  }
  
  const rowId = row.id;
  if (!rowId) {
    console.error('状态切换失败：row.id 不存在', row);
    return;
  }
  
  // 使用传入的 status 或 row.status
  const newStatus = status !== undefined ? status : (row.status ?? 0);
  
  // 获取版本号，优先使用 version_code，否则使用 ID
  const versionCode = row.version_code || `ID:${rowId}` || '未知版本';
  
  await useHandleData(
    changeVersionStatusApi,
    { id: rowId, status: newStatus },
    `切换版本【${versionCode}】状态`
  );
  proTable.value?.getTableList();
};

// 打开 drawer(新增、编辑)
const drawerRef = ref<InstanceType<typeof VersionDrawer> | null>(null);
const openDrawer = (title: string, row: Partial<VersionItem> = {}) => {
  const params = {
    title,
    row: { ...row },
    isView: title === "查看",
    api: title === "新增" ? addVersionApi : title === "编辑" ? updateVersionApi : undefined,
    getTableList: proTable.value?.getTableList
  };
  drawerRef.value?.acceptParams(params);
};
</script>

<style scoped lang="scss">
.download-url-cell {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}
</style>

