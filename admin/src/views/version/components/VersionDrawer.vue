<template>
  <el-drawer v-model="drawerVisible" :destroy-on-close="true" size="600px" :title="`${drawerProps.title}版本`">
    <el-form
      ref="ruleFormRef"
      label-width="100px"
      label-suffix=" :"
      :rules="rules"
      :disabled="drawerProps.isView"
      :model="formData"
      :hide-required-asterisk="drawerProps.isView"
    >
      <el-form-item label="版本号" prop="version_code">
        <el-input
          v-model="formData.version_code"
          placeholder="请输入版本号，如：1.0.0"
          clearable
          maxlength="50"
          show-word-limit
        ></el-input>
        <div class="form-tip">格式：x.xx.xx，如：1.0.0、1.1.0、2.0.0</div>
      </el-form-item>
      <el-form-item label="版本数字" prop="version_number">
        <el-input-number
          v-model="formData.version_number"
          :min="1"
          :max="999999"
          placeholder="请输入版本数字，用于版本比较"
          style="width: 100%"
        />
        <div class="form-tip">版本数字用于版本比较，数字越大版本越新。如果不填写，将根据版本号自动生成</div>
      </el-form-item>
      <el-form-item label="平台" prop="platform">
        <el-select v-model="formData.platform" placeholder="请选择平台" clearable>
          <el-option label="Android" value="android" />
          <el-option label="iOS" value="ios" />
        </el-select>
      </el-form-item>
      <el-form-item label="渠道编号" prop="channel_code">
        <el-input
          v-model="formData.channel_code"
          placeholder="请输入渠道编号，如：channel_001"
          clearable
          maxlength="50"
          show-word-limit
        ></el-input>
        <div class="form-tip">每个渠道的包不一样，留空表示默认渠道</div>
      </el-form-item>
      <el-form-item label="包名" prop="package_name">
        <el-input
          v-model="formData.package_name"
          placeholder="请输入包名，如：com.example.app"
          clearable
          maxlength="200"
          show-word-limit
        ></el-input>
      </el-form-item>
      <el-form-item label="下载地址" prop="download_url">
        <el-input
          v-model="formData.download_url"
          placeholder="请输入下载地址，如：https://example.com/app.apk"
          clearable
          maxlength="500"
        ></el-input>
        <div class="form-tip">请输入完整的下载URL地址</div>
      </el-form-item>
      <el-form-item label="更新日志" prop="update_log">
        <el-input
          v-model="formData.update_log"
          type="textarea"
          :rows="6"
          placeholder="请输入更新日志"
          clearable
        ></el-input>
      </el-form-item>
      <el-form-item label="强制更新" prop="force_update">
        <el-radio-group v-model="formData.force_update">
          <el-radio :label="0">否</el-radio>
          <el-radio :label="1">是</el-radio>
        </el-radio-group>
        <div class="form-tip">选择"是"时，客户端必须更新才能继续使用</div>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-radio-group v-model="formData.status">
          <el-radio :label="0">禁用</el-radio>
          <el-radio :label="1">启用</el-radio>
        </el-radio-group>
        <div class="form-tip">只有启用的版本才会返回给客户端</div>
      </el-form-item>
      <el-form-item label="排序" prop="sort">
        <el-input-number v-model="formData.sort" :min="0" :max="9999" />
        <div class="form-tip">数字越大越靠前，默认0</div>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="drawerVisible = false">取消</el-button>
      <el-button v-show="!drawerProps.isView" type="primary" @click="handleSubmit">确定</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts" name="VersionDrawer">
import { ref, reactive, watch, nextTick } from "vue";
import { ElMessage, FormInstance } from "element-plus";
import { VersionItem, VersionParams } from "@/api/modules/version";

// 版本号验证规则
const validateVersionCode = (rule: any, value: string, callback: any) => {
  if (!value) {
    callback(new Error("请输入版本号"));
    return;
  }
  // 版本号格式验证：x.xx.xx
  const versionPattern = /^\d+\.\d+\.\d+$/;
  if (!versionPattern.test(value)) {
    callback(new Error("版本号格式不正确，应为：x.xx.xx，如：1.0.0"));
    return;
  }
  callback();
};

// URL验证规则（可选）
const validateUrl = (rule: any, value: string, callback: any) => {
  if (value && value.trim()) {
    // URL格式验证
    const urlPattern = /^(https?|ftp):\/\/([^\s/$.?#].[^\s]*)$/i;
    if (!urlPattern.test(value)) {
      callback(new Error("请输入正确的URL格式，如：https://example.com/app.apk"));
      return;
    }
  }
  callback();
};

const rules = reactive({
  version_code: [{ required: true, validator: validateVersionCode, trigger: "blur" }],
  platform: [{ required: true, message: "请选择平台", trigger: "change" }],
  download_url: [{ validator: validateUrl, trigger: "blur" }]
});

interface DrawerProps {
  title: string;
  isView: boolean;
  row: Partial<VersionItem>;
  api?: (params: VersionParams) => Promise<any>;
  getTableList?: () => void;
}

const drawerVisible = ref(false);
const drawerProps = ref<DrawerProps>({
  isView: false,
  title: "",
  row: {}
});

// 表单数据
const formData = reactive<VersionParams>({
  version_code: "",
  version_number: undefined,
  platform: "android",
  channel_code: "",
  package_name: "",
  download_url: "",
  update_log: "",
  force_update: 0,
  status: 1,
  sort: 0
});

// 监听抽屉显示，初始化数据
watch(
  () => drawerVisible.value,
  async val => {
    if (val) {
      // 判断是新增还是编辑模式
      const isEditMode = drawerProps.value.title === "编辑" && drawerProps.value.row.id;

      // 先重置表单数据为默认值
      const defaultData: VersionParams = {
        version_code: "",
        version_number: undefined,
        platform: "android",
        channel_code: "",
        package_name: "",
        download_url: "",
        update_log: "",
        force_update: 0,
        status: 1,
        sort: 0
      };

      if (isEditMode) {
        // 编辑模式：填充表单数据
        Object.assign(formData, {
          ...defaultData,
          id: drawerProps.value.row.id,
          version_code: drawerProps.value.row.version_code ?? "",
          version_number: drawerProps.value.row.version_number ?? undefined,
          platform: drawerProps.value.row.platform ?? "android",
          channel_code: drawerProps.value.row.channel_code ?? "",
          package_name: drawerProps.value.row.package_name ?? "",
          download_url: drawerProps.value.row.download_url ?? "",
          update_log: drawerProps.value.row.update_log ?? "",
          force_update: drawerProps.value.row.force_update ?? 0,
          status: drawerProps.value.row.status ?? 1,
          sort: drawerProps.value.row.sort ?? 0
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
      const submitData: VersionParams = { ...formData };

      // 如果是新增模式，确保不包含id字段
      const isAddMode = drawerProps.value.title === "新增";
      if (isAddMode) {
        delete (submitData as any).id;
      }

      await drawerProps.value.api!(submitData);
      ElMessage.success({ message: `${drawerProps.value.title}版本成功！` });
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

