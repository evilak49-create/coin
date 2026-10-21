<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Version;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    
    
        /**
     * 获取最新版本信息
     * 客户端自行判断是否需要更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function checkUpdate(Request $request)
    {
        $request->validate([
            'platform' => 'nullable|string|in:android,ios', // 平台，默认android
            'channel_code' => 'nullable|string|max:50', // 渠道编号
        ]);

        $platform = $request->get('platform', 'android');
        $channelCode = $request->get('channel_code', '');

        // 获取最新版本（只查询启用的版本）
        $query = Version::query()
            ->where('platform', $platform)
            ->where('status', Version::STATUS_ENABLED);
        
        // 如果有渠道编号，则按渠道筛选；否则获取默认渠道（channel_code为空）的版本
        if (!empty($channelCode)) {
            $query->where('channel_code', $channelCode);
        } else {
            $query->where(function($q) {
                $q->whereNull('channel_code')->orWhere('channel_code', '');
            });
        }
        
        $latestVersion = $query->orderBy('version_number', 'desc')->first();

        if (empty($latestVersion)) {
            return $this->fail(404, '暂无可用版本');
        }

        // 直接返回最新版本信息，客户端自行判断是否需要更新
        return $this->success([
            'id' => $latestVersion->id,
            'version_code' => $latestVersion->version_code,
            'version_number' => $latestVersion->version_number,
            'package_name' => $latestVersion->package_name ?? '',
            'download_url' => $latestVersion->download_url ?? '',
            'update_log' => $latestVersion->update_log ?? '',
            'force_update' => $latestVersion->force_update == 1,
            'download_count' => $latestVersion->download_count ?? 0
        ]);
    }
    
    
    /**
     * 获取最新版本（Android和iOS）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function latest(Request $request)
    {
        $request->validate([
            'channel_code' => 'nullable|string|max:50', // 渠道编号
        ]);

        $channelCode = $request->get('channel_code', '');

        $result = [];

        // 获取Android和iOS两个平台的最新版本
        foreach ([Version::PLATFORM_ANDROID, Version::PLATFORM_IOS] as $platform) {
            $query = Version::query()
                ->where('platform', $platform)
                ->where('status', Version::STATUS_ENABLED);
            
            // 如果有渠道编号，则按渠道筛选；否则获取默认渠道（channel_code为空）的版本
            if (!empty($channelCode)) {
                $query->where('channel_code', $channelCode);
            } else {
                $query->where(function($q) {
                    $q->whereNull('channel_code')->orWhere('channel_code', '');
                });
            }
            
            $latestVersion = $query->orderBy('version_number', 'desc')->first();

            if ($latestVersion) {
                $result[$platform] = [
                    'id' => $latestVersion->id,
                    'version_code' => $latestVersion->version_code,
                    'version_number' => $latestVersion->version_number,
                    'package_name' => $latestVersion->package_name ?? '',
                    'download_url' => $latestVersion->download_url ?? '',
                    'update_log' => $latestVersion->update_log ?? '',
                    'force_update' => $latestVersion->force_update == 1,
                    'download_count' => $latestVersion->download_count ?? 0
                ];
            } else {
                $result[$platform] = null;
            }
        }

        return $this->success($result);
    }
    
    /**
     * 更新下载次数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function updateDownloadCount(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        // 将下载次数加1
        Version::query()->where('id', $request->id)->increment('download_count');

        return $this->success();
    }
}

