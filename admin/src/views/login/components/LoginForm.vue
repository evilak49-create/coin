<template>
  <el-form ref="loginFormRef" :model="loginForm" :rules="loginRules" size="large">
    <el-form-item prop="username">
      <el-input v-model="loginForm.username" placeholder="用户名">
        <template #prefix>
          <el-icon class="el-input__icon">
            <user />
          </el-icon>
        </template>
      </el-input>
    </el-form-item>
    <el-form-item prop="password">
      <el-input v-model="loginForm.password" type="password" placeholder="密码" show-password autocomplete="new-password">
        <template #prefix>
          <el-icon class="el-input__icon">
            <lock />
          </el-icon>
        </template>
      </el-input>
    </el-form-item>
    <el-form-item prop="captcha_code">
      <div class="captcha-wrapper">
        <el-input v-model="loginForm.captcha_code" placeholder="验证码" class="captcha-input" maxlength="10" clearable>
          <template #prefix>
            <el-icon class="el-input__icon">
              <Key />
            </el-icon>
          </template>
        </el-input>
        <div class="captcha-image" @click="refreshCaptcha">
          <img v-if="captchaImage" :src="captchaImage" alt="验证码" />
          <div v-else class="captcha-loading">加载中...</div>
        </div>
      </div>
    </el-form-item>
  </el-form>
  <div class="login-btn">
    <el-button :icon="CircleClose" round size="large" @click="resetForm(loginFormRef)"> 重置 </el-button>
    <el-button :icon="UserFilled" round size="large" type="primary" :loading="loading" @click="login(loginFormRef)">
      登录
    </el-button>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { HOME_URL } from "@/config";
// import { getTimeState } from "@/utils";
import { Login } from "@/api/interface";
import { ElNotification, ElMessage } from "element-plus";
import { loginApi, getCaptchaApi, getUserInfoApi } from "@/api/modules/login";
import { useUserStore } from "@/stores/modules/user";
import { useTabsStore } from "@/stores/modules/tabs";
import { useKeepAliveStore } from "@/stores/modules/keepAlive";
import { initDynamicRouter } from "@/routers/modules/dynamicRouter";
import { CircleClose, UserFilled, Key } from "@element-plus/icons-vue";
import type { ElForm } from "element-plus";
import md5 from "md5";

const router = useRouter();
const userStore = useUserStore();
const tabsStore = useTabsStore();
const keepAliveStore = useKeepAliveStore();

type FormInstance = InstanceType<typeof ElForm>;
const loginFormRef = ref<FormInstance>();
const loginRules = reactive({
  username: [{ required: true, message: "请输入用户名", trigger: "blur" }],
  password: [{ required: true, message: "请输入密码", trigger: "blur" }],
  captcha_code: [
    { required: true, message: "请输入验证码", trigger: ["blur", "change"] },
    { min: 1, message: "验证码不能为空", trigger: ["blur", "change"] }
  ]
});

const loading = ref(false);
const captchaImage = ref<string>("");
const captchaKey = ref<string>("");
const loginForm = reactive<Login.ReqLoginForm>({
  username: "",
  password: "",
  captcha_code: "",
  captcha_key: ""
});

// 获取验证码
const refreshCaptcha = async () => {
  try {
    const response = await getCaptchaApi();
    const captchaData = response.data;

    // 处理验证码key
    if (captchaData.captcha_key) {
      captchaKey.value = captchaData.captcha_key;
      loginForm.captcha_key = captchaData.captcha_key;
    }

    // 处理验证码图片
    if (captchaData.image) {
      const imageData = captchaData.image;
      // 判断是base64字符串还是URL
      if (imageData.startsWith("data:image") || imageData.startsWith("http://") || imageData.startsWith("https://")) {
        // 已经是完整的data URI或URL
        captchaImage.value = imageData;
      } else {
        // 是base64字符串，需要添加前缀
        captchaImage.value = `data:image/png;base64,${imageData}`;
      }
    }

    // 清空验证码输入
    loginForm.captcha_code = "";
  } catch (error: any) {
    console.error("获取验证码失败:", error);
    ElMessage.error(error?.msg || "获取验证码失败，请重试");
  }
};

// login
const login = (formEl: FormInstance | undefined) => {
  if (!formEl) return;
  formEl.validate(async valid => {
    if (!valid) return;
    loading.value = true;
    try {
      // 1.执行登录接口
      const { data } = await loginApi({
        username: loginForm.username,
        password: md5(loginForm.password),
        captcha_code: loginForm.captcha_code,
        captcha_key: captchaKey.value
      });
      userStore.setToken(data.access_token);

      // 2.添加动态路由
      await initDynamicRouter();

      // 3.清空 tabs、keepAlive 数据
      tabsStore.setTabs([]);
      keepAliveStore.setKeepAliveName([]);

      // 4.跳转到首页
      router.push(HOME_URL);
      ElNotification({
        title: "登录成功",
        message: "欢迎登录 Coin管理系统",
        type: "success",
        duration: 3000
      });
    } catch (error: any) {
      // 登录失败，刷新验证码
      if (error?.code !== "401") {
        refreshCaptcha();
      }
    } finally {
      loading.value = false;
    }
  });
};

// resetForm
const resetForm = (formEl: FormInstance | undefined) => {
  if (!formEl) return;
  formEl.resetFields();
  refreshCaptcha();
};

onMounted(() => {
  // 初始化验证码
  refreshCaptcha();
  // 监听 enter 事件（调用登录）
  document.onkeydown = (e: KeyboardEvent) => {
    if (e.code === "Enter" || e.code === "enter" || e.code === "NumpadEnter") {
      if (loading.value) return;
      login(loginFormRef.value);
    }
  };
});

onBeforeUnmount(() => {
  document.onkeydown = null;
});
</script>

<style scoped lang="scss">
@import "../index";
</style>
