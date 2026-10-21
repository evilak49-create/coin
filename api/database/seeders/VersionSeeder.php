<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'version_code' => '1.0.2',
                'version_number' => 2,
                'platform' => 'android',
                'channel_code' => '5AFund',
                'package_name' => 'com.a5.fund',
                'download_url' => 'https://a.fileoss.top/0/5AFund_v1.2.5_15_release.apk',
                'update_log' => null,
                'force_update' => 0,
                'status' => 1,
                'sort' => 0,
                'created_at' => '2025-12-31 18:25:27',
                'updated_at' => '2026-01-03 17:11:52',
            ],
            [
                'id' => 2,
                'version_code' => '1.0.1',
                'version_number' => 1,
                'platform' => 'android',
                'channel_code' => 'sdsd',
                'package_name' => '',
                'download_url' => 'https://dsd.com',
                'update_log' => null,
                'force_update' => 1,
                'status' => 1,
                'sort' => 0,
                'created_at' => '2025-12-31 18:27:00',
                'updated_at' => '2026-01-02 13:05:46',
            ],
        ];

        foreach ($data as $row) {
            DB::table('versions')->insert($row);
        }
    }
}
