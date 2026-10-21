<template>
  <div class="fullscreen">
    <i :class="['iconfont', isFullscreen ? 'icon-suoxiao' : 'icon-fangda']" class="toolBar-icon" @click="handleFullScreen"></i>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";
import { ElMessage } from "element-plus";
import screenfull from "screenfull";

const isFullscreen = ref(screenfull.isFullscreen);

// 监听全屏状态变化
const handleFullscreenChange = () => {
  isFullscreen.value = screenfull.isFullscreen;
};

onMounted(() => {
  // screenfull v6 不再支持 .on() 方法，改用 DOM 事件监听全屏状态变化
  if (screenfull.isEnabled) {
    // 监听标准全屏事件
    document.addEventListener("fullscreenchange", handleFullscreenChange);
    // 监听 WebKit 浏览器全屏事件（Safari, Chrome 旧版本）
    document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
    // 监听 Mozilla 浏览器全屏事件（Firefox）
    document.addEventListener("mozfullscreenchange", handleFullscreenChange);
    // 监听 MS 浏览器全屏事件（IE/Edge 旧版本）
    document.addEventListener("MSFullscreenChange", handleFullscreenChange);
  }
});

onUnmounted(() => {
  // 清理事件监听器
  if (screenfull.isEnabled) {
    document.removeEventListener("fullscreenchange", handleFullscreenChange);
    document.removeEventListener("webkitfullscreenchange", handleFullscreenChange);
    document.removeEventListener("mozfullscreenchange", handleFullscreenChange);
    document.removeEventListener("MSFullscreenChange", handleFullscreenChange);
  }
});

const handleFullScreen = () => {
  if (!screenfull.isEnabled) ElMessage.warning("当前您的浏览器不支持全屏 ❌");
  screenfull.toggle();
};
</script>
