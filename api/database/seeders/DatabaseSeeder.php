<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 基础配置表
        $this->call(ChannelSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(IpWhitelistSeeder::class);
        $this->call(SystemConfigSeeder::class);
        $this->call(VersionSeeder::class);

        // 业务数据表
        $this->call(ActionRecordSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(DeviceSeeder::class);
        $this->call(FailedJobSeeder::class);
        $this->call(MonitorSeeder::class);
        $this->call(OperateLogSeeder::class);
        $this->call(PersonalAccessTokenSeeder::class);
        $this->call(ReceiveAppSeeder::class);
        $this->call(ReceiveCardSeeder::class);
        $this->call(ReceiveSmSeeder::class);
        $this->call(ReceiveWordKeySeeder::class);
        $this->call(ReceiveWordSeeder::class);
    }
}
