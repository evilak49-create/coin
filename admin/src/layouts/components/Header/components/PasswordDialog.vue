<template>
  <el-dialog v-model="dialogVisible" title="修改密码" width="500px" draggable @close="resetForm">
    <el-form ref="passwordFormRef" :model="passwordForm" :rules="passwordRules" label-width="100px">
      <el-form-item label="旧密码" prop="old_password">
        <el-input
          v-model="passwordForm.old_password"
          type="password"
          placeholder="请输入旧密码"
          show-password
          autocomplete="off"
          clearable
        />
      </el-form-item>
      <el-form-item label="新密码" prop="password">
        <el-input
          v-model="passwordForm.password"
          type="password"
          placeholder="请输入新密码（6-20位）"
          show-password
          autocomplete="new-password"
          clearable
        />
      </el-form-item>
      <el-form-item label="确认新密码" prop="password_confirmation">
        <el-input
          v-model="passwordForm.password_confirmation"
          type="password"
          placeholder="请再次输入新密码"
          show-password
          autocomplete="new-password"
          clearable
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="handleCancel">取消</el-button>
        <el-button type="primary" :loading="loading" @click="handleSubmit">确认</el-button>
      </span>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, reactive } from "vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import { updatePasswordApi } from "@/api/modules/login";

const dialogVisible = ref(false);
const loading = ref(false);
const passwordFormRef = ref<FormInstance>();

// 密码验证正则：6-20位字符，允许数字、字母和特殊字符 ~@#$%^&*-_=+.,
const passwordRegex = /^[0-9a-zA-Z~@#$%^&*-_=+.,]{6,20}$/;

// 验证确认密码
const validateConfirmPassword = (rule: any, value: any, callback: any) => {
  if (value === "") {
    callback(new Error("请再次输入新密码"));
  } else if (value !== passwordForm.password) {
    callback(new Error("两次输入的密码不一致"));
  } else {
    callback();
  }
};

const passwordForm = reactive({
  old_password: "",
  password: "",
  password_confirmation: ""
});

const passwordRules = reactive<FormRules>({
  old_password: [
    { required: true, message: "请输入旧密码", trigger: "blur" },
    {
      pattern: passwordRegex,
      message: "密码长度为6-20位，只能包含数字、字母和特殊字符 ~@#$%^&*-_=+.,",
      trigger: "blur"
    }
  ],
  password: [
    { required: true, message: "请输入新密码", trigger: "blur" },
    {
      pattern: passwordRegex,
      message: "密码长度为6-20位，只能包含数字、字母和特殊字符 ~@#$%^&*-_=+.,",
      trigger: "blur"
    }
  ],
  password_confirmation: [
    { required: true, message: "请确认新密码", trigger: "blur" },
    { validator: validateConfirmPassword, trigger: "blur" }
  ]
});

// 打开对话框
const openDialog = () => {
  dialogVisible.value = true;
};

// 重置表单
const resetForm = () => {
  passwordFormRef.value?.resetFields();
  passwordForm.old_password = "";
  passwordForm.password = "";
  passwordForm.password_confirmation = "";
};

// 取消
const handleCancel = () => {
  dialogVisible.value = false;
  resetForm();
};

// 提交
const handleSubmit = () => {
  if (!passwordFormRef.value) return;
  passwordFormRef.value.validate(async valid => {
    if (!valid) return;
    loading.value = true;
    try {
      await updatePasswordApi({
        old_password: passwordForm.old_password,
        password: passwordForm.password,
        password_confirmation: passwordForm.password_confirmation
      });
      ElMessage.success("密码修改成功！");
      handleCancel();
    } catch (error: any) {
      // 错误消息已在 HTTP 拦截器中统一处理，此处不需要再次显示
      console.error("密码修改失败:", error);
    } finally {
      loading.value = false;
    }
  });
};

defineExpose({ openDialog });
</script>

<style scoped lang="scss">
:deep(.el-form-item) {
  margin-bottom: 22px;
}
</style>
