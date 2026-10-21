<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpWhitelistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'ip' => '127.0.0.1',
                'type' => 'whitelist',
                'description' => '2233交换机',
                'status' => 1,
                'created_at' => '2025-12-05 16:16:56',
                'updated_at' => '2025-12-05 16:58:58',
            ],
            [
                'id' => 3,
                'ip' => '1.1.1.1',
                'type' => 'whitelist',
                'description' => '232',
                'status' => 1,
                'created_at' => '2025-12-10 21:47:21',
                'updated_at' => '2025-12-10 21:47:21',
            ],
            [
                'id' => 4,
                'ip' => '172.18.0.1',
                'type' => 'whitelist',
                'description' => '本地',
                'status' => 1,
                'created_at' => '2025-12-11 12:22:56',
                'updated_at' => '2025-12-11 12:22:56',
            ],
        ];

        foreach ($data as $row) {
            DB::table('ip_whitelists')->insert($row);
        }
    }
}
