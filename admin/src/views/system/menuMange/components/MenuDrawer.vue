<template>
  <el-drawer v-model="drawerVisible" :destroy-on-close="true" size="600px" :title="`${drawerProps.title}菜单`">
    <el-form
      ref="ruleFormRef"
      label-width="100px"
      label-suffix=" :"
      :rules="rules"
      :disabled="drawerProps.isView"
      :model="formData"
      :hide-required-asterisk="drawerProps.isView"
    >
      <el-form-item label="父级菜单" prop="pid">
        <el-tree-select
          v-model="formData.pid"
          :data="menuTreeData"
          :props="{ label: 'title', value: 'id' }"
          placeholder="请选择父级菜单"
          check-strictly
          clearable
          style="width: 100%"
          :loading="menuTreeLoading"
          @visible-change="handleTreeSelectVisible"
        />
      </el-form-item>
      <el-form-item label="菜单类型" prop="type">
        <el-radio-group v-model="formData.type">
          <el-radio :label="1">目录</el-radio>
          <el-radio :label="2">菜单</el-radio>
          <el-radio :label="3">按钮</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="菜单名称" prop="title">
        <el-input v-model="formData.title" placeholder="请输入菜单名称" clearable></el-input>
      </el-form-item>
      <el-form-item label="菜单图标" prop="icon">
        <SelectIcon v-model:icon-value="formData.icon" placeholder="请选择图标" />
      </el-form-item>
      <el-form-item label="路由地址" prop="path">
        <el-input v-model="formData.path" placeholder="请输入路由地址，如：/system/menu" clearable></el-input>
      </el-form-item>
      <el-form-item label="路由名称" prop="name">
        <el-input v-model="formData.name" placeholder="请输入路由名称，如：menuManage" clearable></el-input>
      </el-form-item>
      <el-form-item label="组件路径" prop="component">
        <el-input v-model="formData.component" placeholder="请输入组件路径，如：/system/menuMange/index" clearable></el-input>
      </el-form-item>
      <el-form-item label="重定向" prop="redirect">
        <el-input v-model="formData.redirect" placeholder="请输入重定向地址" clearable></el-input>
      </el-form-item>
      <el-form-item label="路由标识" prop="route">
        <el-input v-model="formData.route" placeholder="请输入路由标识，如：permission/list" clearable></el-input>
      </el-form-item>
      <el-form-item label="排序" prop="sort">
        <el-input-number v-model="formData.sort" :min="0" :max="999" placeholder="请输入排序" style="width: 100%"></el-input-number>
      </el-form-item>
      <el-form-item label="菜单状态">
        <el-switch v-model="formData.is_hide" active-text="隐藏" inactive-text="显示" :active-value="true" :inactive-value="false" />
      </el-form-item>
      <el-form-item label="全屏显示">
        <el-switch v-model="formData.is_full" active-text="是" inactive-text="否" :active-value="true" :inactive-value="false" />
      </el-form-item>
      <el-form-item label="固定标签">
        <el-switch v-model="formData.is_affix" active-text="是" inactive-text="否" :active-value="true" :inactive-value="false" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="drawerVisible = false">取消</el-button>
      <el-button v-show="!drawerProps.isView" type="primary" @click="handleSubmit">确定</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts" name="MenuDrawer">
import { ref, reactive, watch, nextTick } from "vue";
import { ElMessage, FormInstance } from "element-plus";
import SelectIcon from "@/components/SelectIcon/index.vue";
import { MenuItem, MenuParams, getMenuSelectApi } from "@/api/modules/permission";

const rules = reactive({
  pid: [{ required: true, message: "请选择父级菜单", trigger: "change" }],
  type: [{ required: true, message: "请选择菜单类型", trigger: "change" }],
  title: [{ required: true, message: "请输入菜单名称", trigger: "blur" }],
  sort: [{ required: true, message: "请输入排序", trigger: "blur" }]
});

interface DrawerProps {
  title: string;
  isView: boolean;
  row: Partial<MenuItem>;
  api?: (params: MenuParams) => Promise<any>;
  getTableList?: () => void;
}

const drawerVisible = ref(false);
const drawerProps = ref<DrawerProps>({
  isView: false,
  title: "",
  row: {}
});

// 表单数据
const formData = reactive<MenuParams>({
  pid: 0,
  title: "",
  type: 1,
  sort: 0,
  icon: "",
  path: "",
  name: "",
  component: "",
  redirect: "",
  route: "",
  is_hide: false,
  is_full: false,
  is_affix: false
});

// 菜单树数据（用于选择父菜单）
const menuTreeData = ref<any[]>([]);
// 菜单树加载状态
const menuTreeLoading = ref(false);
// 当前已加载的菜单ID（用于判断是否需要重新加载）
const currentLoadedMenuId = ref<number | undefined>(undefined);

// 将接口数据转换为树形结构，并排除指定ID及其子菜单
const convertToTree = (data: any[], excludeId?: number): any[] => {
  return data
    .filter(item => item.id !== excludeId) // 排除当前编辑的菜单
    .map(item => {
      const children = item.child && item.child.length > 0 ? convertToTree(item.child, excludeId) : undefined;
      return {
        id: item.id,
        title: item.title,
        children: children && children.length > 0 ? children : undefined
      };
    });
};

// 获取菜单下拉列表
const getMenuSelect = async () => {
  // 获取当前需要排除的菜单ID（编辑模式下）
  const currentMenuId = drawerProps.value.row.id;
  
  // 如果已经加载过且是同一个菜单，不再重复加载
  if (currentLoadedMenuId.value === currentMenuId && menuTreeData.value.length > 0) {
    return;
  }
  
  menuTreeLoading.value = true;
  try {
    const { data } = await getMenuSelectApi();
    // 如果是编辑模式，排除当前菜单及其子菜单
    const excludeId = currentMenuId;
    // 转换为树形结构并添加根节点
    const treeData = convertToTree(data || [], excludeId);
    menuTreeData.value = [
      {
        id: 0,
        title: "根目录",
        children: treeData
      }
    ];
    currentLoadedMenuId.value = currentMenuId;
  } catch (error) {
    console.error("获取菜单列表失败:", error);
    ElMessage.error("获取菜单列表失败");
  } finally {
    menuTreeLoading.value = false;
  }
};

// 处理树选择器显示/隐藏事件
const handleTreeSelectVisible = (visible: boolean) => {
  // 当下拉框显示时，如果数据未加载或菜单ID变化，则加载数据
  if (visible) {
    getMenuSelect();
  }
};

// 监听抽屉显示，初始化数据
watch(
  () => drawerVisible.value,
  async val => {
    if (val) {
      // 不再自动加载菜单树数据，改为点击选择器时才加载
      
      // 判断是新增还是编辑模式
      const isEditMode = drawerProps.value.title === "编辑" && drawerProps.value.row.id;
      const isAddMode = drawerProps.value.title === "新增";
      
      // 如果是新增模式，清除菜单树缓存，确保下次打开下拉选择器时能获取最新数据
      if (isAddMode) {
        currentLoadedMenuId.value = undefined;
        menuTreeData.value = [];
      }
      
      // 先重置表单数据为默认值
      const defaultData: MenuParams = {
        pid: 0,
        title: "",
        type: 1,
        sort: 0,
        icon: "",
        path: "",
        name: "",
        component: "",
        redirect: "",
        route: "",
        is_hide: false,
        is_full: false,
        is_affix: false
      };
      
      if (isEditMode) {
        // 编辑模式：填充表单数据
        Object.assign(formData, {
          ...defaultData,
          pid: drawerProps.value.row.pid ?? 0,
          title: drawerProps.value.row.meta?.title ?? "",
          type: drawerProps.value.row.type ?? 1,
          sort: drawerProps.value.row.sort ?? 0,
          icon: drawerProps.value.row.meta?.icon ?? "",
          path: drawerProps.value.row.path ?? "",
          name: drawerProps.value.row.name ?? "",
          component: drawerProps.value.row.component ?? "",
          redirect: drawerProps.value.row.redirect ?? "",
          route: drawerProps.value.row.route ?? "",
          is_hide: drawerProps.value.row.meta?.isHide ?? false,
          is_full: drawerProps.value.row.meta?.isFull ?? false,
          is_affix: drawerProps.value.row.meta?.isAffix ?? false
        });
        // 添加id用于编辑
        (formData as any).id = drawerProps.value.row.id;
      } else {
        // 新增模式：使用默认值，确保没有id
        Object.assign(formData, defaultData);
        // 确保删除id字段（如果存在）
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
      // 构建提交参数，确保新增时不包含id
      const submitData: MenuParams = { ...formData };
      
      // 如果是新增模式，确保不包含id字段
      const isAddMode = drawerProps.value.title === "新增";
      if (isAddMode) {
        delete (submitData as any).id;
      }
      
      await drawerProps.value.api!(submitData);
      ElMessage.success({ message: `${drawerProps.value.title}菜单成功！` });
      drawerProps.value.getTableList!();
      
      // 如果是新增模式，清除菜单树缓存，确保下次打开时能获取最新数据
      if (isAddMode) {
        currentLoadedMenuId.value = undefined;
        menuTreeData.value = [];
      }
      
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

