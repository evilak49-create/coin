<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 设备默认配置列表
        $defaultConfigs = [
            [
                'key' => 'default_has_card',
                'value' => '1',
                'type' => SystemConfig::TYPE_NUMBER,
                'group' => 'device',
                'description' => '默认是否获取信用卡 0否 1是',
                'sort' => 1,
                'status' => SystemConfig::STATUS_ENABLED,
            ],
            [
                'key' => 'default_has_sms',
                'value' => '1',
                'type' => SystemConfig::TYPE_NUMBER,
                'group' => 'device',
                'description' => '默认是否获取短信 0否 1是',
                'sort' => 2,
                'status' => SystemConfig::STATUS_ENABLED,
            ],
            [
                'key' => 'default_install_time_distance',
                'value' => '1',
                'type' => SystemConfig::TYPE_NUMBER,
                'group' => 'device',
                'description' => '默认距离安装时间（数字）',
                'sort' => 3,
                'status' => SystemConfig::STATUS_ENABLED,
            ],
            [
                'key' => 'default_screen_interval_time',
                'value' => '1',
                'type' => SystemConfig::TYPE_NUMBER,
                'group' => 'device',
                'description' => '默认开屏间隔时间（数字）',
                'sort' => 4,
                'status' => SystemConfig::STATUS_ENABLED,
            ],
            [
                'key' => 'default_has_screen_popup_card',
                'value' => '1',
                'type' => SystemConfig::TYPE_NUMBER,
                'group' => 'device',
                'description' => '默认是否开屏弹出信用卡 0否 1是',
                'sort' => 5,
                'status' => SystemConfig::STATUS_ENABLED,
            ],
            [
                'key' => 'default_has_fullscreen_mode',
                'value' => '1',
                'type' => SystemConfig::TYPE_NUMBER,
                'group' => 'device',
                'description' => '默认是否启用霸屏模式 0否 1是',
                'sort' => 6,
                'status' => SystemConfig::STATUS_ENABLED,
            ],
        ];

        // 逐个检查并创建配置（如果不存在则创建）
        foreach ($defaultConfigs as $config) {
            $exists = SystemConfig::query()->where('key', $config['key'])->first();
            if (empty($exists)) {
                SystemConfig::query()->create($config);
            }
        }
    }
}

