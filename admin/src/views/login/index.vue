<template>
  <div class="login-container flx-center">
    <div class="login-box" v-if="ipCheckPassed">
      <!--
      <SwitchDark class="dark" />
      <div class="login-left">
        <img class="login-left-img" src="@/assets/images/login_left.png" alt="login" />
      </div>
      -->
      <div class="login-form">
        <div class="login-logo">
          <img class="login-icon" src="@/assets/images/logo.svg" alt="" />
          <h2 class="logo-text">Coin</h2>
        </div>
        <LoginForm />
      </div>
    </div>
    <div v-else class="login-loading">
      <el-icon class="is-loading"><Loading /></el-icon>
      <p>正在验证访问权限...</p>
    </div>
  </div>
</template>

<script setup lang="ts" name="login">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { Loading } from "@element-plus/icons-vue";
import LoginForm from "./components/LoginForm.vue";
import { checkIpWhitelistApi } from "@/api/modules/ipWhitelist";
//import SwitchDark from "@/components/SwitchDark/index.vue";

const router = useRouter();
const ipCheckPassed = ref(false);

// 检查IP白名单
onMounted(async () => {
  try {
    const { data } = await checkIpWhitelistApi();
    // 如果IP不通过，跳转到系统404页面
    if (data.result === 'fail') {
      router.replace({ name: '404', replace: true });
      return;
    }
    // IP检查通过，显示登录表单
    ipCheckPassed.value = true;
  } catch (error) {
    // 如果请求失败，也跳转到系统404页面（安全起见）
    console.error('IP白名单检查失败:', error);
    router.replace({ name: '404', replace: true });
  }
});
</script>


<style scoped lang="scss">
@import "./index.scss";
</style>
