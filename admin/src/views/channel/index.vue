<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="渠道列表"
      row-key="id"
      :columns="columns"
      :request-api="getChannelListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <!-- 表格 header 按钮 -->
      <template #tableHeader>
        <el-button type="primary" :icon="CirclePlus" @click="openDrawer('新增')">新增渠道</el-button>
      </template>
      <!-- 操作 -->
      <template #operation="scope">
        <el-button type="primary" link :icon="EditPen" @click="openDrawer('编辑', scope.row)">编辑</el-button>
        <el-button type="primary" link :icon="Delete" @click="deleteChannel(scope.row)">删除</el-button>
      </template>
    </ProTable>
    <ChannelDrawer ref="drawerRef" />
  </div>
</template>

<script setup lang="ts" name="channel">
import { ref, reactive } from "vue";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { Delete, EditPen, CirclePlus } from "@element-plus/icons-vue";
import ProTable from "@/components/ProTable/index.vue";
import ChannelDrawer from "./components/ChannelDrawer.vue";
import {
  getChannelListApi,
  addChannelApi,
  updateChannelApi,
  deleteChannelApi,
  ChannelItem,
  ChannelListResponse
} from "@/api/modules/channel";
import { useHandleData } from "@/hooks/useHandleData";

const proTable = ref<ProTableInstance>();
const loading = ref(false);

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的渠道列表请求
const getChannelListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getChannelListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: ChannelListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<ChannelItem>[]>([
  { prop: "code", label: "编号", width: 150, search: { el: "input", props: { placeholder: "请输入编号" } } },
  { prop: "name", label: "名称", width: 200, search: { el: "input", props: { placeholder: "请输入名称" } } },
  { prop: "url", label: "网址", width: 300, search: { el: "input", props: { placeholder: "请输入网址" } } },
  { prop: "device_count", label: "设备数量", width: 120 },
  { prop: "remark", label: "备注", width: 200 },
  { prop: "created_at", label: "创建时间", width: 180 },
  { prop: "updated_at", label: "更新时间", width: 180 },
  { prop: "operation", label: "操作", width: 200, fixed: "right" }
]);

// 删除渠道
const deleteChannel = async (row: ChannelItem) => {
  await useHandleData(deleteChannelApi, { id: row.id }, `删除渠道【${row.name}】`);
  proTable.value?.getTableList();
};

// 打开 drawer(新增、编辑)
const drawerRef = ref<InstanceType<typeof ChannelDrawer> | null>(null);
const openDrawer = (title: string, row: Partial<ChannelItem> = {}) => {
  const params = {
    title,
    row: { ...row },
    isView: title === "查看",
    api: title === "新增" ? addChannelApi : title === "编辑" ? updateChannelApi : undefined,
    getTableList: proTable.value?.getTableList
  };
  drawerRef.value?.acceptParams(params);
};
</script>

