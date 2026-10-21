<template>
  <div class="tool-bar-ri">
    <div class="header-icon">
      <!--<AssemblySize id="assemblySize" />-->
      <!--<Language id="language" />-->
      <!--<SearchMenu id="searchMenu" />-->
      <!--<ThemeSetting id="themeSetting" />-->
      <!--<Message id="message" />-->
      <Fullscreen id="fullscreen" />
    </div>
    <div class="online-device">
      <el-icon><Monitor /></el-icon>
      <span class="count">{{ onlineDeviceCount }}</span>
    </div>
    <span class="username">{{ username }}</span>
    <Avatar />
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useUserStore } from "@/stores/modules/user";
import { Monitor } from "@element-plus/icons-vue";
import { getOnlineDeviceCountApi } from "@/api/modules/device";
import AssemblySize from "./components/AssemblySize.vue";
import Language from "./components/Language.vue";
import SearchMenu from "./components/SearchMenu.vue";
import ThemeSetting from "./components/ThemeSetting.vue";
import Message from "./components/Message.vue";
import Fullscreen from "./components/Fullscreen.vue";
import Avatar from "./components/Avatar.vue";

const userStore = useUserStore();
const username = computed(() => userStore.userInfo?.username || "");

// 在线设备数量
const onlineDeviceCount = ref(0);
let refreshTimer: ReturnType<typeof setInterval> | null = null;

// 获取在线设备数量
const fetchOnlineCount = async () => {
  try {
    const { data } = await getOnlineDeviceCountApi();
    onlineDeviceCount.value = data.online_count;
  } catch (error) {
    console.error("获取在线设备数量失败:", error);
  }
};

// 组件挂载时开始定时刷新
onMounted(() => {
  // 立即获取一次
  fetchOnlineCount();
  // 每30秒刷新一次
  refreshTimer = setInterval(fetchOnlineCount, 30000);
});

// 组件卸载时清除定时器
onUnmounted(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
});
</script>

<style scoped lang="scss">
.tool-bar-ri {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-right: 25px;
  .header-icon {
    display: flex;
    align-items: center;
    & > * {
      margin-left: 21px;
      color: var(--el-header-text-color);
    }
  }
  .online-device {
    display: flex;
    align-items: center;
    margin-left: 20px;
    padding: 4px 12px;
    background: linear-gradient(135deg, #67c23a 0%, #85ce61 100%);
    border-radius: 16px;
    color: #fff;
    font-size: 13px;
    .el-icon {
      margin-right: 4px;
      font-size: 14px;
    }
    .count {
      font-weight: 600;
    }
  }
  .username {
    margin: 0 20px;
    font-size: 15px;
    color: var(--el-header-text-color);
  }
}
</style>
