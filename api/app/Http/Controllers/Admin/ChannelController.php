<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;

class ChannelController extends Controller
{

    /**
     * 渠道列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'nullable|int',
            'limit' => 'nullable|int',
            'code' => 'nullable|string',
            'name' => 'nullable|string',
            'url' => 'nullable|string',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $filters = [];
        if (!empty($request->code)) {
            $filters['code'] = $request->code;
        }
        if (!empty($request->name)) {
            $filters['name'] = $request->name;
        }
        if (!empty($request->url)) {
            $filters['url'] = $request->url;
        }

        $result = Channel::getList($filters, $page, $limit);

        return $this->success($result);
    }

    /**
     * 获取指定渠道
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = Channel::getById($request->id);

        if (empty($info)) {
            return $this->fail(404, '渠道不存在');
        }

        return $this->success($info);
    }

    /**
     * 添加渠道
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'url' => 'required|string|max:255|url',
            'remark' => 'nullable|string',
        ]);

        // 检查编号是否已存在
        $exists = Channel::query()->where('code', $request->code)->first();
        if ($exists) {
            return $this->fail(400, '该编号已存在');
        }

        $data = [
            'code' => $request->code,
            'name' => $request->name,
            'url' => $request->url,
            'remark' => $request->get('remark', ''),
        ];

        $channel = Channel::create($data);

        return $this->success($channel->toArray());
    }

    /**
     * 更新渠道
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'code' => 'nullable|string|max:50',
            'name' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255|url',
            'remark' => 'nullable|string',
        ]);

        $channel = Channel::query()->find($request->id);
        if (empty($channel)) {
            return $this->fail(404, '渠道不存在');
        }

        // 如果更新编号，检查是否与其他记录重复
        if ($request->has('code')) {
            $exists = Channel::query()
                ->where('code', $request->code)
                ->where('id', '!=', $request->id)
                ->first();
            if ($exists) {
                return $this->fail(400, '该编号已存在');
            }
        }

        $data = [];
        if ($request->has('code')) {
            $data['code'] = $request->code;
        }
        if ($request->has('name')) {
            $data['name'] = $request->name;
        }
        if ($request->has('url')) {
            $data['url'] = $request->url;
        }
        if ($request->has('remark')) {
            $data['remark'] = $request->remark;
        }

        $channel->update($data);
        // 确保 updated_at 字段被更新
        $channel->touch();

        return $this->success($channel->toArray());
    }

    /**
     * 删除渠道
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $channel = Channel::query()->find($request->id);
        if (empty($channel)) {
            return $this->fail(404, '渠道不存在');
        }

        $channel->delete();

        return $this->success();
    }

}

