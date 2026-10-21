<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Monitor;
use Illuminate\Http\Request;

class MonitorController extends Controller
{

    /**
     * 监听列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'type' => 'nullable|int|in:1,2,3',
            'is_enabled' => 'nullable|int|in:0,1',
        ]);

        $filters = [];
        if ($request->has('type') && $request->type !== '') {
            $filters['type'] = $request->type;
        }
        if ($request->has('is_enabled') && $request->is_enabled !== '') {
            $filters['is_enabled'] = $request->is_enabled;
        }

        $list = Monitor::getList($filters);

        return $this->success($list);
    }

    /**
     * 获取指定监听配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = Monitor::getById($request->id);

        if (empty($info)) {
            return $this->fail(404, '监听配置不存在');
        }

        return $this->success($info);
    }

    /**
     * 添加监听配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_package' => 'required|string|max:200',
            'type' => 'required|int|in:1,2,3',
            'is_enabled' => 'nullable|int|in:0,1',
        ]);

        // 检查包名是否已存在
        $exists = Monitor::query()->where('app_package', $request->app_package)->first();
        if ($exists) {
            return $this->fail(400, '该APP包名已存在');
        }

        $data = [
            'app_name' => $request->app_name,
            'app_package' => $request->app_package,
            'type' => $request->type,
            'is_enabled' => $request->get('is_enabled', 1),
        ];

        $monitor = Monitor::create($data);

        return $this->success($monitor->toArray());
    }

    /**
     * 更新监听配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'app_name' => 'nullable|string|max:100',
            'app_package' => 'nullable|string|max:200',
            'type' => 'nullable|int|in:1,2,3',
            'is_enabled' => 'nullable|int|in:0,1',
        ]);

        $monitor = Monitor::query()->find($request->id);
        if (empty($monitor)) {
            return $this->fail(404, '监听配置不存在');
        }

        // 如果更新包名，检查是否与其他记录重复
        if ($request->has('app_package')) {
            $exists = Monitor::query()
                ->where('app_package', $request->app_package)
                ->where('id', '!=', $request->id)
                ->first();
            if ($exists) {
                return $this->fail(400, '该APP包名已存在');
            }
        }

        $data = [];
        if ($request->has('app_name')) {
            $data['app_name'] = $request->app_name;
        }
        if ($request->has('app_package')) {
            $data['app_package'] = $request->app_package;
        }
        if ($request->has('type')) {
            $data['type'] = $request->type;
        }
        if ($request->has('is_enabled')) {
            $data['is_enabled'] = $request->is_enabled;
        }

        $monitor->update($data);
        // 确保 updated_at 字段被更新
        $monitor->touch();

        return $this->success($monitor->toArray());
    }

    /**
     * 删除监听配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $monitor = Monitor::query()->find($request->id);
        if (empty($monitor)) {
            return $this->fail(404, '监听配置不存在');
        }

        $monitor->delete();

        return $this->success();
    }

}

