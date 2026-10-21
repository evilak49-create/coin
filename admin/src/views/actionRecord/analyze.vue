<template>
  <div class="analyze-box" v-loading="loading" element-loading-text="加载中...">
    <div class="filter-box">
      <el-date-picker
        v-model="operateDate"
        type="date"
        placeholder="选择操作日期"
        format="YYYY-MM-DD"
        value-format="YYYY-MM-DD"
        @change="handleDateChange"
      />
      <el-button type="primary" :icon="Search" @click="loadData">查询</el-button>
    </div>

    <el-table :data="tableData" border stripe style="width: 100%">
      <el-table-column prop="operate_date" label="操作日期" width="120" fixed="left" />
      <el-table-column prop="app_name" label="APP名称" min-width="150" show-overflow-tooltip />
      
      <el-table-column label="提醒统计" align="center">
        <el-table-column prop="reminder_count" label="助记词提醒次数" width="140" align="center" />
        <el-table-column prop="reminder_confirm_count" label="助记词确认次数" width="140" align="center" />
        <el-table-column prop="reminder_back_count" label="返回次数" width="120" align="center" />
      </el-table-column>
      
      <el-table-column label="输入统计" align="center">
        <el-table-column prop="input_show_count" label="助记词导入展示次数" width="160" align="center" />
        <el-table-column prop="input_confirm_count" label="输入次数" width="120" align="center" />
        <el-table-column prop="input_back_count" label="返回次数" width="120" align="center" />
      </el-table-column>
      
      <el-table-column label="信用卡统计" align="center">
        <el-table-column prop="card_show_count" label="信用卡展示次数" width="140" align="center" />
        <el-table-column prop="card_confirm_count" label="输入次数" width="120" align="center" />
        <el-table-column prop="card_back_count" label="返回次数" width="120" align="center" />
      </el-table-column>
    </el-table>
  </div>
</template>

<script setup lang="ts" name="actionRecordAnalyze">
import { ref, onMounted } from "vue";
import { Search } from "@element-plus/icons-vue";
import { getActionRecordAnalyzeApi, ActionAnalyzeItem } from "@/api/modules/actionRecord";
import { ElMessage } from "element-plus";

const loading = ref(false);
const operateDate = ref<string>(new Date().toISOString().split('T')[0]);
const tableData = ref<ActionAnalyzeItem[]>([]);

// 加载数据
const loadData = async () => {
  loading.value = true;
  try {
    const result = await getActionRecordAnalyzeApi({ operate_date: operateDate.value });
    tableData.value = result.data.list || [];
    if (tableData.value.length === 0) {
      ElMessage.info("暂无数据");
    }
  } catch (error) {
    ElMessage.error("获取统计数据失败");
  } finally {
    loading.value = false;
  }
};

// 日期变化处理
const handleDateChange = () => {
  loadData();
};

// 初始化加载
onMounted(() => {
  loadData();
});
</script>

<style scoped lang="scss">
.analyze-box {
  padding: 20px;
}

.filter-box {
  margin-bottom: 20px;
  display: flex;
  gap: 10px;
  align-items: center;
}
</style>

