<template>
  <el-drawer v-model="drawerVisible" :destroy-on-close="true" size="600px" :title="`${drawerProps.title}渠道`">
    <el-form
      ref="ruleFormRef"
      label-width="100px"
      label-suffix=" :"
      :rules="rules"
      :disabled="drawerProps.isView"
      :model="formData"
      :hide-required-asterisk="drawerProps.isView"
    >
      <el-form-item label="编号" prop="code">
        <el-input
          v-model="formData.code"
          placeholder="请输入编号"
          clearable
          maxlength="50"
          show-word-limit
        ></el-input>
      </el-form-item>
      <el-form-item label="名称" prop="name">
        <el-input
          v-model="formData.name"
          placeholder="请输入名称"
          clearable
          maxlength="100"
          show-word-limit
        ></el-input>
      </el-form-item>
      <el-form-item label="网址" prop="url">
        <el-input
          v-model="formData.url"
          placeholder="请输入网址，如：https://www.example.com"
          clearable
          maxlength="255"
        ></el-input>
        <div class="form-tip">请输入完整的URL地址，如：https://www.example.com</div>
      </el-form-item>
      <el-form-item label="备注" prop="remark">
        <el-input
          v-model="formData.remark"
          type="textarea"
          :rows="4"
          placeholder="请输入备注信息"
          clearable
        ></el-input>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="drawerVisible = false">取消</el-button>
      <el-button v-show="!drawerProps.isView" type="primary" @click="handleSubmit">确定</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts" name="ChannelDrawer">
import { ref, reactive, watch, nextTick } from "vue";
import { ElMessage, FormInstance } from "element-plus";
import { ChannelItem, ChannelParams } from "@/api/modules/channel";

// URL验证规则
const validateUrl = (rule: any, value: string, callback: any) => {
  if (!value) {
    callback(new Error("请输入网址"));
    return;
  }
  // URL格式验证
  const urlPattern = /^(https?|ftp):\/\/([^\s/$.?#].[^\s]*)$/i;
  if (!urlPattern.test(value)) {
    callback(new Error("请输入正确的URL格式，如：https://www.example.com"));
    return;
  }
  callback();
};

const rules = reactive({
  code: [{ required: true, message: "请输入编号", trigger: "blur" }],
  name: [{ required: true, message: "请输入名称", trigger: "blur" }],
  url: [{ required: true, validator: validateUrl, trigger: "blur" }]
});

interface DrawerProps {
  title: string;
  isView: boolean;
  row: Partial<ChannelItem>;
  api?: (params: ChannelParams) => Promise<any>;
  getTableList?: () => void;
}

const drawerVisible = ref(false);
const drawerProps = ref<DrawerProps>({
  isView: false,
  title: "",
  row: {}
});

// 表单数据
const formData = reactive<ChannelParams>({
  code: "",
  name: "",
  url: "",
  remark: ""
});

// 监听抽屉显示，初始化数据
watch(
  () => drawerVisible.value,
  async val => {
    if (val) {
      // 判断是新增还是编辑模式
      const isEditMode = drawerProps.value.title === "编辑" && drawerProps.value.row.id;
      
      // 先重置表单数据为默认值
      const defaultData: ChannelParams = {
        code: "",
        name: "",
        url: "",
        remark: ""
      };
      
      if (isEditMode) {
        // 编辑模式：填充表单数据
        Object.assign(formData, {
          ...defaultData,
          id: drawerProps.value.row.id,
          code: drawerProps.value.row.code ?? "",
          name: drawerProps.value.row.name ?? "",
          url: drawerProps.value.row.url ?? "",
          remark: drawerProps.value.row.remark ?? ""
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
      const submitData: ChannelParams = { ...formData };
      
      // 如果是新增模式，确保不包含id字段
      const isAddMode = drawerProps.value.title === "新增";
      if (isAddMode) {
        delete (submitData as any).id;
      }
      
      await drawerProps.value.api!(submitData);
      ElMessage.success({ message: `${drawerProps.value.title}渠道成功！` });
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

