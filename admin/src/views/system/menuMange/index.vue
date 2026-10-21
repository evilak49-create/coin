<template>
  <div class="table-box" v-loading="loading" element-loading-text="加载中...">
    <ProTable
      ref="proTable"
      title="菜单列表"
      row-key="id"
      :indent="20"
      :columns="columns"
      :request-api="getMenuListWithLoading"
      :pagination="false"
      :data-callback="dataCallback"
      :tree-props="{ children: 'children' }"
      default-expand-all
    >
      <!-- 表格 header 按钮 -->
      <template #tableHeader>
        <el-button type="primary" :icon="CirclePlus" @click="openDrawer('新增')">新增菜单</el-button>
      </template>
      <!-- 菜单图标 -->
      <template #icon="scope">
        <el-icon v-if="scope.row.meta?.icon" :size="18">
          <component :is="scope.row.meta.icon"></component>
        </el-icon>
        <span v-else>-</span>
      </template>
      <!-- 菜单类型 -->
      <template #type="scope">
        <el-tag :type="getTypeTagType(scope.row.type) || undefined">
          {{ getTypeText(scope.row.type) }}
        </el-tag>
      </template>
      <!-- 菜单状态 -->
      <template #isHide="scope">
        <el-tag :type="scope.row.meta?.isHide ? 'info' : 'success'">
          {{ scope.row.meta?.isHide ? "隐藏" : "显示" }}
        </el-tag>
      </template>
      <!-- 排序字段 -->
      <template #sort="scope">
        <el-input-number
          v-model="scope.row.sort"
          :min="0"
          :max="999"
          :step="1"
          size="small"
          controls-position="right"
          style="width: 100px"
          @change="handleSortChange(scope.row)"
        />
      </template>
      <!-- 菜单操作 -->
      <template #operation="scope">
        <el-button type="primary" link :icon="EditPen" @click="openDrawer('编辑', scope.row)">编辑</el-button>
        <el-button type="primary" link :icon="Delete" @click="deleteMenu(scope.row)">删除</el-button>
      </template>
    </ProTable>
    <MenuDrawer ref="drawerRef" />
  </div>
</template>

<script setup lang="ts" name="menuMange">
import { ref, reactive } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { ColumnProps, ProTableInstance } from "@/components/ProTable/interface";
import { Delete, EditPen, CirclePlus } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import ProTable from "@/components/ProTable/index.vue";
import MenuDrawer from "./components/MenuDrawer.vue";
import { getMenuListApi, addMenuApi, updateMenuApi, deleteMenuApi, MenuItem, MenuParams } from "@/api/modules/permission";
import { useHandleData } from "@/hooks/useHandleData";

const proTable = ref<ProTableInstance>();
const loading = ref(false);

// 带加载状态的菜单列表请求
const getMenuListWithLoading = async (params?: any) => {
  loading.value = true;
  try {
    const result = await getMenuListApi();
    return result;
  } finally {
    loading.value = false;
  }
};


// 获取菜单类型文本
const getTypeText = (type: number) => {
  const typeMap: Record<number, string> = {
    1: "目录",
    2: "菜单",
    3: "按钮"
  };
  return typeMap[type] || "未知";
};

// 获取菜单类型标签类型
const getTypeTagType = (type: number): "success" | "warning" | "info" | "danger" | undefined => {
  const typeMap: Record<number, "success" | "warning" | "info" | "danger" | undefined> = {
    1: undefined,
    2: "success",
    3: "warning"
  };
  return typeMap[type];
};

// 表格配置项
const columns = reactive<ColumnProps<MenuItem>[]>([
  { prop: "meta.title", label: "菜单名称", align: "left", width: 200, search: { el: "input" } },
  { prop: "meta.icon", label: "菜单图标", width: 100 },
  { prop: "type", label: "菜单类型", width: 100 },
  { prop: "path", label: "路由地址", width: 200, search: { el: "input" } },
  { prop: "name", label: "路由名称", width: 150 },
  { prop: "component", label: "组件路径", width: 200 },
  { prop: "route", label: "路由标识", width: 150 },
  { prop: "sort", label: "排序", width: 80 },
  { prop: "isHide", label: "菜单状态", width: 100 },
  { prop: "operation", label: "操作", width: 200, fixed: "right" }
]);

// 删除菜单
const deleteMenu = async (row: MenuItem) => {
  await useHandleData(deleteMenuApi, { id: row.id }, `删除【${row.meta?.title}】菜单`);
  proTable.value?.getTableList();
};

// 存储原始排序值，用于失败时恢复
const originalSortMap = new Map<number, number>();

// 处理排序变化（带防抖）
const handleSortChange = useDebounceFn(async (row: MenuItem) => {
  // 如果值没有变化，不提交
  const originalSort = originalSortMap.get(row.id);
  if (originalSort !== undefined && originalSort === row.sort) {
    return;
  }

  try {
    // 构建更新参数
    const updateParams: MenuParams = {
      id: row.id,
      pid: row.pid,
      title: row.meta?.title || "",
      sort: row.sort,
      type: row.type,
      route: row.route || "",
      path: row.path || "",
      name: row.name || "",
      component: row.component || "",
      redirect: row.redirect || "",
      icon: row.meta?.icon || "",
      is_hide: row.meta?.isHide || false,
      is_full: row.meta?.isFull || false,
      is_affix: row.meta?.isAffix || false
    };
    
    await updateMenuApi(updateParams);
    // 更新成功后，更新原始值
    originalSortMap.set(row.id, row.sort);
    ElMessage.success(`【${row.meta?.title}】排序已更新`);
  } catch (error) {
    // 如果更新失败，恢复原值
    if (originalSort !== undefined) {
      row.sort = originalSort;
    } else {
      proTable.value?.getTableList();
    }
    ElMessage.error("更新排序失败，请重试");
    console.error("更新排序失败:", error);
  }
}, 800);

// 初始化原始排序值
const initOriginalSort = (data: MenuItem[]) => {
  const traverse = (items: MenuItem[]) => {
    items.forEach(item => {
      originalSortMap.set(item.id, item.sort);
      if (item.children && item.children.length > 0) {
        traverse(item.children);
      }
    });
  };
  traverse(data);
};

// 修改数据回调，初始化原始排序值
const dataCallback = (data: any) => {
  const list = Array.isArray(data) ? data : [];
  initOriginalSort(list);
  return list;
};

// 打开 drawer(新增、查看、编辑)
const drawerRef = ref<InstanceType<typeof MenuDrawer> | null>(null);
const openDrawer = (title: string, row: Partial<MenuItem> = {}) => {
  const params = {
    title,
    row: { ...row },
    isView: title === "查看",
    api: title === "新增" ? addMenuApi : title === "编辑" ? updateMenuApi : undefined,
    getTableList: proTable.value?.getTableList
  };
  drawerRef.value?.acceptParams(params);
};
</script>
