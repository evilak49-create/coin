<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Version;
use Illuminate\Http\Request;

class VersionController extends Controller
{

    /**
     * 版本列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'nullable|int',
            'limit' => 'nullable|int',
            'version_code' => 'nullable|string',
            'platform' => 'nullable|string|in:android,ios',
            'channel_code' => 'nullable|string',
            'status' => 'nullable|int|in:0,1',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $filters = [];
        if (!empty($request->version_code)) {
            $filters['version_code'] = $request->version_code;
        }
        if (!empty($request->platform)) {
            $filters['platform'] = $request->platform;
        }
        if (!empty($request->channel_code)) {
            $filters['channel_code'] = $request->channel_code;
        }
        if (isset($request->status) && $request->status !== '') {
            $filters['status'] = $request->status;
        }

        $result = Version::getList($filters, $page, $limit);

        return $this->success($result);
    }

    /**
     * 获取指定版本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = Version::getById($request->id);

        if (empty($info)) {
            return $this->fail(404, '版本不存在');
        }

        return $this->success($info);
    }

    /**
     * 添加版本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'version_code' => 'required|string|max:50',
            'version_number' => 'nullable|int|min:1',
            'platform' => 'required|string|in:android,ios',
            'channel_code' => 'nullable|string|max:50',
            'package_name' => 'nullable|string|max:200',
            'download_url' => 'nullable|string|max:500|url',
            'update_log' => 'nullable|string',
            'force_update' => 'nullable|int|in:0,1',
            'status' => 'nullable|int|in:0,1',
            'sort' => 'nullable|int',
        ]);

        // 如果提供了版本数字，使用提供的；否则自动生成
        $versionNumber = $request->get('version_number');
        if (empty($versionNumber)) {
            $versionNumber = Version::versionCodeToNumber($request->version_code);
        }

        // 检查该平台和渠道是否已存在相同版本号
        $channelCode = $request->get('channel_code', '');
        $exists = Version::query()
            ->where('platform', $request->platform)
            ->where('channel_code', $channelCode)
            ->where('version_code', $request->version_code)
            ->first();
        if ($exists) {
            return $this->fail(400, '该平台和渠道已存在相同版本号');
        }

        $data = [
            'version_code' => $request->version_code,
            'version_number' => $versionNumber,
            'platform' => $request->platform,
            'channel_code' => $channelCode,
            'package_name' => $request->get('package_name', ''),
            'download_url' => $request->get('download_url', ''),
            'update_log' => $request->get('update_log', ''),
            'force_update' => $request->get('force_update', 0),
            'status' => $request->get('status', 1),
            'sort' => $request->get('sort', 0),
        ];

        $version = Version::create($data);

        return $this->success($version->toArray());
    }

    /**
     * 更新版本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'version_code' => 'required|string|max:50',
            'version_number' => 'nullable|int|min:1',
            'platform' => 'required|string|in:android,ios',
            'channel_code' => 'nullable|string|max:50',
            'download_url' => 'nullable|string|max:500|url',
            'update_log' => 'nullable|string',
            'force_update' => 'nullable|int|in:0,1',
            'status' => 'nullable|int|in:0,1',
            'sort' => 'nullable|int',
        ]);

        $version = Version::query()->where('id', $request->id)->first();
        if (empty($version)) {
            return $this->fail(404, '版本不存在');
        }

        // 如果提供了版本数字，使用提供的；否则自动生成
        $versionNumber = $request->get('version_number');
        if (empty($versionNumber)) {
            $versionNumber = Version::versionCodeToNumber($request->version_code);
        }

        // 检查该平台和渠道是否已存在相同版本号（排除自己）
        $channelCode = $request->get('channel_code', '');
        $exists = Version::query()
            ->where('platform', $request->platform)
            ->where('channel_code', $channelCode)
            ->where('version_code', $request->version_code)
            ->where('id', '!=', $request->id)
            ->first();
        if ($exists) {
            return $this->fail(400, '该平台和渠道已存在相同版本号');
        }

        $data = [
            'version_code' => $request->version_code,
            'version_number' => $versionNumber,
            'platform' => $request->platform,
            'channel_code' => $channelCode,
            'package_name' => $request->get('package_name', ''),
            'download_url' => $request->get('download_url', ''),
            'update_log' => $request->get('update_log', ''),
            'force_update' => $request->get('force_update', 0),
            'status' => $request->get('status', 1),
            'sort' => $request->get('sort', 0),
        ];

        $version->update($data);

        return $this->success($version->toArray());
    }

    /**
     * 删除版本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $version = Version::query()->where('id', $request->id)->first();
        if (empty($version)) {
            return $this->fail(404, '版本不存在');
        }

        $version->delete();

        return $this->success();
    }

    /**
     * 批量删除版本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|int'
        ]);

        Version::query()->whereIn('id', $request->ids)->delete();

        return $this->success();
    }

    /**
     * 切换版本状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function changeStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'status' => 'required|int|in:0,1'
        ]);

        $version = Version::query()->where('id', $request->id)->first();
        if (empty($version)) {
            return $this->fail(404, '版本不存在');
        }

        $version->update(['status' => $request->status]);

        return $this->success($version->toArray());
    }
}

