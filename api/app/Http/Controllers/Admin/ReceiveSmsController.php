<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReceiveSms;
use Illuminate\Http\Request;

class ReceiveSmsController extends Controller
{
    /**
     * 短信列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'required|int',
            'limit' => 'required|int',
            'device_number' => 'nullable|string',
            'sender' => 'nullable|string',
            'content' => 'nullable|string',
            'device_id' => 'nullable|int',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $model = ReceiveSms::query();

        // 按设备号搜索
        if (!empty($request->device_number)) {
            $model->where('device_number', 'like', '%' . $request->device_number . '%');
        }

        // 按发送方搜索
        if (!empty($request->sender)) {
            $model->where('sender', 'like', '%' . $request->sender . '%');
        }

        // 按设备ID搜索
        if (!empty($request->device_id)) {
            $model->where('device_id', $request->device_id);
        }

        $count = $model->count();
        $list = $model->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return $this->success(['total_num' => $count, 'list' => $list]);
    }

    /**
     * 获取指定短信
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = ReceiveSms::query()
            ->where('id', $request->id)
            ->first();

        if (empty($info)) {
            return $this->fail(404, '短信不存在');
        }

        return $this->success($info->toArray());
    }

    /**
     * 删除指定短信
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $sms = ReceiveSms::query()->where('id', $request->id)->first();
        if (empty($sms)) {
            return $this->fail(404, '短信不存在');
        }

        ReceiveSms::query()->where('id', $request->id)->delete();
        return $this->success();
    }

    /**
     * 批量删除短信
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
            return $this->fail(400, '请选择要删除的短信');
        }

        $count = ReceiveSms::query()->whereIn('id', $request->ids)->delete();
        
        return $this->success(['deleted_count' => $count]);
    }
}

