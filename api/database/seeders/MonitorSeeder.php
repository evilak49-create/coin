<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 7,
                'app_name' => 'OKX',
                'app_package' => 'com.okinc.okex.gp',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:24:47',
                'updated_at' => '2025-12-11 11:24:47',
            ],
            [
                'id' => 8,
                'app_name' => '谷歌钱包',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:25:03',
                'updated_at' => '2025-12-11 11:25:03',
            ],
            [
                'id' => 9,
                'app_name' => '币安',
                'app_package' => 'com.binance.dev',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:25:18',
                'updated_at' => '2026-01-06 13:52:35',
            ],
            [
                'id' => 10,
                'app_name' => 'Trust Wallet',
                'app_package' => 'com.wallet.crypto.trustapp',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:25:34',
                'updated_at' => '2025-12-11 11:25:34',
            ],
            [
                'id' => 11,
                'app_name' => 'TronLink Pro',
                'app_package' => 'com.tronlinkpro.wallet',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:25:47',
                'updated_at' => '2025-12-11 11:25:47',
            ],
            [
                'id' => 12,
                'app_name' => 'CoinBase',
                'app_package' => 'org.toshi',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:26:04',
                'updated_at' => '2025-12-11 11:26:04',
            ],
            [
                'id' => 13,
                'app_name' => 'MetaMask',
                'app_package' => 'io.metamask',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:26:16',
                'updated_at' => '2025-12-11 11:26:16',
            ],
            [
                'id' => 14,
                'app_name' => 'imToken',
                'app_package' => 'im.token.app',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:26:29',
                'updated_at' => '2025-12-11 11:26:29',
            ],
            [
                'id' => 15,
                'app_name' => 'TokenPocket',
                'app_package' => 'vip.mytokenpocket',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:26:41',
                'updated_at' => '2025-12-11 11:26:41',
            ],
            [
                'id' => 16,
                'app_name' => 'TronLink Global',
                'app_package' => 'com.tronlink.global',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:26:53',
                'updated_at' => '2025-12-11 11:26:53',
            ],
            [
                'id' => 17,
                'app_name' => 'PayPal',
                'app_package' => 'com.paypal.android.p2pmobile',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:27:09',
                'updated_at' => '2025-12-11 11:27:09',
            ],
            [
                'id' => 18,
                'app_name' => 'Amazon Shopping',
                'app_package' => 'cn.amazon.mShop.android',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:27:25',
                'updated_at' => '2025-12-11 11:27:25',
            ],
            [
                'id' => 19,
                'app_name' => 'Uber',
                'app_package' => 'com.ubercab',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:27:39',
                'updated_at' => '2025-12-11 11:27:39',
            ],
            [
                'id' => 20,
                'app_name' => 'Temu',
                'app_package' => 'com.einnovation.temu',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:27:51',
                'updated_at' => '2025-12-11 11:27:51',
            ],
            [
                'id' => 21,
                'app_name' => 'Lazada',
                'app_package' => 'com.lazada.android',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:28:06',
                'updated_at' => '2025-12-11 11:28:06',
            ],
            [
                'id' => 22,
                'app_name' => 'SHEIN',
                'app_package' => 'com.zzkko',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:28:18',
                'updated_at' => '2025-12-11 11:28:18',
            ],
            [
                'id' => 23,
                'app_name' => 'PayPal Merchant',
                'app_package' => 'com.paypal.merchant.client',
                'type' => 2,
                'is_enabled' => 1,
                'deleted_at' => null,
                'created_at' => '2025-12-11 11:28:30',
                'updated_at' => '2026-01-02 12:04:55',
            ],
            [
                'id' => 24,
                'app_name' => 'dftgdfg',
                'app_package' => 'com.sdgs.dsdsf',
                'type' => 1,
                'is_enabled' => 1,
                'deleted_at' => '2026-01-02 12:04:16',
                'created_at' => '2026-01-02 12:04:01',
                'updated_at' => '2026-01-02 12:04:16',
            ],
        ];

        foreach ($data as $row) {
            DB::table('monitors')->insert($row);
        }
    }
}
