<template>
  <el-drawer v-model="drawerVisible" :destroy-on-close="true" size="520px" :title="`${drawerProps.title}用户`">
    <el-form
      ref="ruleFormRef"
      label-width="100px"
      label-suffix=" :"
      :rules="rules"
      :disabled="drawerProps.isView"
      :model="formData"
      :hide-required-asterisk="drawerProps.isView"
    >
      <el-form-item label="用户名" prop="username">
        <el-input v-model="formData.username" placeholder="请输入用户名" clearable />
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-radio-group v-model="formData.status">
          <el-radio :label="0">正常</el-radio>
          <el-radio :label="1">禁用</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="drawerVisible = false">取消</el-button>
      <el-button v-show="!drawerProps.isView" type="primary" @click="handleSubmit">确定</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts" name="UserDrawer">
import { ref, reactive, watch, nextTick } from "vue";
import { ElMessage, FormInstance, FormRules } from "element-plus";
import { AdminUserItem, AdminUserParams } from "@/api/modules/user";

const rules = reactive<FormRules>({
  username: [
    { required: true, message: "请输入用户名", trigger: "blur" },
    { min: 2, max: 30, message: "长度在 2 到 30 个字符", trigger: "blur" }
  ],
  status: [{ required: true, message: "请选择状态", trigger: "change" }],
  // 角色固定为超级管理员，故无需校验选择
  role_ids: []
});

interface DrawerProps {
  title: string;
  isView: boolean;
  row: Partial<AdminUserItem>;
  api?: (params: AdminUserParams) => Promise<any>;
  getTableList?: () => void;
}

const drawerVisible = ref(false);
const drawerProps = ref<DrawerProps>({
  isView: false,
  title: "",
  row: {}
});

// 默认角色：超级管理员
const DEFAULT_ROLE_IDS = [1];

// 表单数据
const formData = reactive<AdminUserParams>({
  username: "",
  status: 0,
  role_ids: DEFAULT_ROLE_IDS
});

// 监听抽屉显示，初始化数据
watch(
  () => drawerVisible.value,
  async val => {
    if (!val) return;

    const defaultData: AdminUserParams = {
      username: "",
      status: 0,
      role_ids: DEFAULT_ROLE_IDS
    };

    // 编辑模式填充
    if (drawerProps.value.title === "编辑" && drawerProps.value.row.id) {
      Object.assign(formData, {
        ...defaultData,
        id: drawerProps.value.row.id,
        username: drawerProps.value.row.username ?? "",
        status: drawerProps.value.row.status ?? 0,
        role_ids:
          drawerProps.value.row.role_ids ??
          (drawerProps.value.row.role ? drawerProps.value.row.role.map(item => item.id) : DEFAULT_ROLE_IDS)
      });
    } else {
      Object.assign(formData, defaultData);
      delete (formData as any).id;
    }

    nextTick(() => {
      ruleFormRef.value?.clearValidate();
    });
  }
);

// 接收父组件传过来的参数
const acceptParams = (params: DrawerProps) => {
  drawerProps.value = params;
  drawerVisible.value = true;
};

// 提交数据（新增/编辑）
const ruleFormRef = ref<FormInstance>();
const handleSubmit = () => {
  ruleFormRef.value!.validate(async valid => {
    if (!valid) return;
    try {
      const submitData: AdminUserParams = { ...formData };
      if (!submitData.id) {
        delete (submitData as any).id;
      }
      await drawerProps.value.api!(submitData);
      ElMessage.success({ message: `${drawerProps.value.title}用户成功！` });
      drawerProps.value.getTableList?.();
      drawerVisible.value = false;
    } catch (error) {
      console.log(error);
    }
  });
};

defineExpose({
  acceptParams
});
</script>

