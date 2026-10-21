<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use Illuminate\Http\Request;

class SystemConfigController extends Controller
{

    /**
     * 系统配置列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'nullable|int',
            'limit' => 'nullable|int',
            'key' => 'nullable|string',
            'group' => 'nullable|string',
            'status' => 'nullable|int|in:0,1',
            'type' => 'nullable|string|in:string,number,image',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $filters = [];
        if (!empty($request->key)) {
            $filters['key'] = $request->key;
        }
        if (!empty($request->group)) {
            $filters['group'] = $request->group;
        }
        if ($request->has('status')) {
            $filters['status'] = $request->status;
        }
        if (!empty($request->type)) {
            $filters['type'] = $request->type;
        }

        $result = SystemConfig::getList($filters, $page, $limit);

        return $this->success($result);
    }

    /**
     * 获取指定配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = SystemConfig::getById($request->id);

        if (empty($info)) {
            return $this->fail(404, '配置不存在');
        }

        return $this->success($info);
    }

    /**
     * 根据key获取配置值
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getByKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string'
        ]);

        $config = SystemConfig::getByKey($request->key, true);

        if (empty($config)) {
            return $this->fail(404, '配置不存在或已禁用');
        }

        return $this->success($config);
    }

    /**
     * 根据分组获取配置列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getByGroup(Request $request)
    {
        $request->validate([
            'group' => 'required|string'
        ]);

        $list = SystemConfig::getListByGroup($request->group, true);

        return $this->success($list);
    }

    /**
     * 获取所有分组列表
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getGroups()
    {
        $groups = SystemConfig::getAllGroups(true);

        return $this->success($groups);
    }


/*
// 添加配置
POST /api/admin/systemConfig/add
{
    "key": "site_name",
    "value": "我的网站",
    "type": "string",
    "group": "basic",
    "description": "网站名称",
    "sort": 1,
    "status": 1
}

// 更新配置
POST /api/admin/systemConfig/update
{
    "id": 1,
    "key": "site_name",
    "value": "新网站名称",
    "type": "string",
    "group": "basic",
    "description": "网站名称",
    "sort": 1,
    "status": 1
}

// 删除配置
POST /api/admin/systemConfig/del
{
    "id": 1
}

// 切换状态
POST /api/admin/systemConfig/changeStatus
{
    "id": 1
}

// 批量删除
POST /api/admin/systemConfig/batchDelete
{
    "ids": [1, 2, 3]
}
*/

    /**
     * 添加配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:100',
            'value' => 'nullable',
            'type' => 'required|string|in:string,number,image',
            'group' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'sort' => 'nullable|int',
            'status' => 'nullable|int|in:0,1',
        ]);

        // 检查key是否已存在
        $exists = SystemConfig::query()->where('key', $request->key)->first();
        if (!empty($exists)) {
            return $this->fail(400, '配置键名已存在');
        }

        SystemConfig::query()->create([
            'key' => $request->key,
            'value' => $request->value ?? '',
            'type' => $request->type,
            'group' => $request->group ?? 'default',
            'description' => $request->description ?? '',
            'sort' => $request->sort ?? 0,
            'status' => $request->status ?? SystemConfig::STATUS_ENABLED,
        ]);

        return $this->success();
    }

    /**
     * 编辑配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'key' => 'required|string|max:100',
            'value' => 'nullable',
            'type' => 'required|string|in:string,number,image',
            'group' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'sort' => 'nullable|int',
            'status' => 'nullable|int|in:0,1',
        ]);

        // 检查key是否被其他配置使用
        $checkKey = SystemConfig::query()
            ->where('key', $request->key)
            ->where('id', '!=', $request->id)
            ->first();
        if (!empty($checkKey)) {
            return $this->fail(400, '配置键名已被其他配置使用');
        }

        $config = SystemConfig::query()->where('id', $request->id)->firstOrFail();
        $config->key = $request->key;
        $config->value = $request->value ?? '';
        $config->type = $request->type;
        $config->group = $request->group ?? 'default';
        $config->description = $request->description ?? '';
        $config->sort = $request->sort ?? 0;
        $config->status = $request->status ?? SystemConfig::STATUS_ENABLED;
        $config->save();

        return $this->success();
    }

    /**
     * 删除配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $config = SystemConfig::query()->where('id', $request->id)->firstOrFail();
        $config->delete();

        return $this->success();
    }

    /**
     * 配置状态切换（启用/禁用）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function changeStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $config = SystemConfig::query()->where('id', $request->id)->firstOrFail();
        $config->status = $config->status == SystemConfig::STATUS_DISABLED 
            ? SystemConfig::STATUS_ENABLED 
            : SystemConfig::STATUS_DISABLED;
        $config->save();

        return $this->success();
    }

    /**
     * 批量删除配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|int'
        ]);

        SystemConfig::query()->whereIn('id', $request->ids)->delete();

        return $this->success();
    }

    /**
     * 批量更新配置值
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     * 
     * // 前端调用示例
        POST /api/admin/systemConfig/batchUpdateValues
        {
            "configs": [
                {"key": "site_name", "value": "我的网站"},
                {"key": "site_url", "value": "https://example.com"},
                {"key": "contact_email", "value": "admin@example.com"}
            ]
        }

        // 响应
        {
            "code": 0,
            "data": {
                "updated": [
                    {"key": "site_name", "value": "我的网站"},
                    {"key": "site_url", "value": "https://example.com"},
                    {"key": "contact_email", "value": "admin@example.com"}
                ],
                "updated_count": 3
            }
        }
     * 
     */
    public function batchUpdateValues(Request $request)
    {
        $request->validate([
            'configs' => 'required|array',
            'configs.*.key' => 'required|string|max:100',
            'configs.*.value' => 'nullable',
        ]);

        $configs = $request->configs;
        $updated = [];
        $notFound = [];

        foreach ($configs as $item) {
            $config = SystemConfig::query()->where('key', $item['key'])->first();
            
            if (empty($config)) {
                $notFound[] = $item['key'];
                continue;
            }

            $config->value = $item['value'] ?? '';
            $config->save();
            
            $updated[] = [
                'key' => $config->key,
                'value' => $config->value,
            ];
        }

        $result = [
            'updated' => $updated,
            'updated_count' => count($updated),
        ];

        if (!empty($notFound)) {
            $result['not_found'] = $notFound;
            $result['not_found_count'] = count($notFound);
        }

        return $this->success($result);
    }

}

