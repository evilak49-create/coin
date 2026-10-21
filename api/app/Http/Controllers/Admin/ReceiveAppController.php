<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReceiveApp;
use Illuminate\Http\Request;

class ReceiveAppController extends Controller
{
    /**
     * 应用列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'required|int',
            'limit' => 'required|int',
            'device_number' => 'nullable|string',
            'app_name' => 'nullable|string',
            'app_package' => 'nullable|string',
            'device_id' => 'nullable|int',
            'install_time' => 'nullable|string',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $model = ReceiveApp::query();

        // 按设备号搜索
        if (!empty($request->device_number)) {
            $model->where('device_number', 'like', '%' . $request->device_number . '%');
        }

        // 按应用名称搜索
        if (!empty($request->app_name)) {
            $model->where('app_name', 'like', '%' . $request->app_name . '%');
        }

        // 按应用包名搜索
        if (!empty($request->app_package)) {
            $model->where('app_package', 'like', '%' . $request->app_package . '%');
        }

        // 按设备ID搜索
        if (!empty($request->device_id)) {
            $model->where('device_id', $request->device_id);
        }

        // 按安装时间搜索
        if (!empty($request->install_time)) {
            $model->where('install_time', 'like', '%' . $request->install_time . '%');
        }

        $count = $model->count();
        $list = $model->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->select('id', 'device_id', 'device_number', 'app_name', 'app_package', 'install_time', 'created_at', 'updated_at')
            ->get()
            ->toArray();

        return $this->success(['total_num' => $count, 'list' => $list]);
    }

    /**
     * 获取指定应用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = ReceiveApp::query()
            ->where('id', $request->id)
            ->first();

        if (empty($info)) {
            return $this->fail(404, '应用不存在');
        }

        return $this->success($info->toArray());
    }

    /**
     * 删除指定应用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $app = ReceiveApp::query()->where('id', $request->id)->first();
        if (empty($app)) {
            return $this->fail(404, '应用不存在');
        }

        ReceiveApp::query()->where('id', $request->id)->delete();
        return $this->success();
    }

    /**
     * 批量删除应用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|int'
        ]);

        if (empty($request->ids)) {
            return $this->fail(400, '请选择要删除的应用');
        }

        $count = ReceiveApp::query()->whereIn('id', $request->ids)->delete();
        
        return $this->success(['deleted_count' => $count]);
    }
}

