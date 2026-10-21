<template>
  <el-drawer v-model="drawerVisible" :destroy-on-close="true" size="600px" :title="`${drawerProps.title}IP白名单`">
    <el-form
      ref="ruleFormRef"
      label-width="100px"
      label-suffix=" :"
      :rules="rules"
      :disabled="drawerProps.isView"
      :model="formData"
      :hide-required-asterisk="drawerProps.isView"
    >
      <el-form-item label="IP地址" prop="ip">
        <el-input
          v-model="formData.ip"
          placeholder="请输入IP地址，支持CIDR格式，如：192.168.1.1 或 192.168.1.0/24"
          clearable
        ></el-input>
        <div class="form-tip">支持单个IP或CIDR格式（如：192.168.1.0/24）</div>
      </el-form-item>
      <el-form-item label="描述" prop="description">
        <el-input
          v-model="formData.description"
          type="textarea"
          :rows="3"
          placeholder="请输入描述信息"
          clearable
        ></el-input>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-radio-group v-model="formData.status">
          <el-radio :label="1">启用</el-radio>
          <el-radio :label="0">禁用</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="drawerVisible = false">取消</el-button>
      <el-button v-show="!drawerProps.isView" type="primary" @click="handleSubmit">确定</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts" name="IpWhitelistDrawer">
import { ref, reactive, watch, nextTick } from "vue";
import { ElMessage, FormInstance } from "element-plus";
import { IpWhitelistItem, IpWhitelistParams } from "@/api/modules/ipWhitelist";

// IP地址验证规则
const validateIp = (rule: any, value: string, callback: any) => {
  if (!value) {
    callback(new Error("请输入IP地址"));
    return;
  }
  // 检查是否为CIDR格式
  if (value.includes("/")) {
    const parts = value.split("/");
    if (parts.length !== 2) {
      callback(new Error("CIDR格式不正确，应为：192.168.1.0/24"));
      return;
    }
    const [subnet, mask] = parts;
    if (!/^(\d{1,3}\.){3}\d{1,3}$/.test(subnet)) {
      callback(new Error("IP地址格式不正确"));
      return;
    }
    const maskNum = parseInt(mask);
    if (isNaN(maskNum) || maskNum < 0 || maskNum > 32) {
      callback(new Error("子网掩码必须在0-32之间"));
      return;
    }
  } else {
    // 单个IP验证
    if (!/^(\d{1,3}\.){3}\d{1,3}$/.test(value)) {
      callback(new Error("IP地址格式不正确"));
      return;
    }
    const parts = value.split(".");
    for (const part of parts) {
      const num = parseInt(part);
      if (isNaN(num) || num < 0 || num > 255) {
        callback(new Error("IP地址格式不正确"));
        return;
      }
    }
  }
  callback();
};

const rules = reactive({
  ip: [{ required: true, validator: validateIp, trigger: "blur" }],
  status: [{ required: true, message: "请选择状态", trigger: "change" }]
});

interface DrawerProps {
  title: string;
  isView: boolean;
  row: Partial<IpWhitelistItem>;
  api?: (params: IpWhitelistParams) => Promise<any>;
  getTableList?: () => void;
}

const drawerVisible = ref(false);
const drawerProps = ref<DrawerProps>({
  isView: false,
  title: "",
  row: {}
});

// 表单数据
const formData = reactive<IpWhitelistParams>({
  ip: "",
  type: "whitelist",
  description: "",
  status: 1
});

// 监听抽屉显示，初始化数据
watch(
  () => drawerVisible.value,
  async val => {
    if (val) {
      // 判断是新增还是编辑模式
      const isEditMode = drawerProps.value.title === "编辑" && drawerProps.value.row.id;
      
      // 先重置表单数据为默认值
      const defaultData: IpWhitelistParams = {
        ip: "",
        type: "whitelist",
        description: "",
        status: 1
      };
      
      if (isEditMode) {
        // 编辑模式：填充表单数据
        Object.assign(formData, {
          ...defaultData,
          id: drawerProps.value.row.id,
          ip: drawerProps.value.row.ip ?? "",
          type: drawerProps.value.row.type ?? "whitelist",
          description: drawerProps.value.row.description ?? "",
          status: drawerProps.value.row.status ?? 1
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
      const submitData: IpWhitelistParams = { ...formData };
      
      // 如果是新增模式，确保不包含id字段
      const isAddMode = drawerProps.value.title === "新增";
      if (isAddMode) {
        delete (submitData as any).id;
      }
      
      await drawerProps.value.api!(submitData);
      ElMessage.success({ message: `${drawerProps.value.title}IP白名单成功！` });
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

