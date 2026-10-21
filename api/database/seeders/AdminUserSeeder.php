<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'username' => 'admin',
                'password' => '$2y$10$mSWv2yOHopuAzcwfmzq3Ke0I3JFt1S3Xvr2OQTo7vgl.z3etL.oZq',
                'avatar' => '',
                'status' => 0,
                'role_ids' => '[1]',
                'ip_last' => '195.86.243.187',
                'login_time_last' => '2026-01-05 15:52:11',
                'deleted_at' => null,
                'created_at' => '2024-10-31 18:49:41',
                'updated_at' => '2026-01-05 15:52:11',
            ],
            [
                'id' => 4,
                'username' => 'admin02',
                'password' => '$2y$10$oOAXFbEk6Oswt8gjHq0cjeL4M7MTkpdFcqVeipvsye6C58USKWEkO',
                'avatar' => '',
                'status' => 0,
                'role_ids' => '[1]',
                'ip_last' => '35.216.44.7',
                'login_time_last' => '2025-12-13 12:47:54',
                'deleted_at' => null,
                'created_at' => '2024-11-03 22:54:03',
                'updated_at' => '2025-12-13 12:47:54',
            ],
            [
                'id' => 157,
                'username' => 'admin3',
                'password' => '$2y$10$qrtk0/HmHT7UrB3pHB/gPO1kfML6suKXVXme2EXHe0TeCpTTNPIey',
                'avatar' => '',
                'status' => 1,
                'role_ids' => '[1]',
                'ip_last' => '',
                'login_time_last' => null,
                'deleted_at' => null,
                'created_at' => '2025-12-09 16:56:15',
                'updated_at' => '2025-12-09 16:59:31',
            ],
            [
                'id' => 158,
                'username' => 'adminx',
                'password' => '$2y$10$0GGoV5XvrZMNV/D2MKsfWeZmyiNYkp66qUUJ077ZAfjvbxM8BCwTu',
                'avatar' => '',
                'status' => 0,
                'role_ids' => '[1]',
                'ip_last' => '',
                'login_time_last' => null,
                'deleted_at' => null,
                'created_at' => '2025-12-10 22:20:37',
                'updated_at' => '2025-12-10 22:20:47',
            ],
            [
                'id' => 159,
                'username' => 'admin4',
                'password' => '$2y$10$DVprg1y73sQ08yHWqq2K2usoGiqHU2C98q3E/mCjckbhL44QO0QhO',
                'avatar' => '',
                'status' => 0,
                'role_ids' => '[1]',
                'ip_last' => '38.75.137.122',
                'login_time_last' => '2026-01-01 22:35:37',
                'deleted_at' => null,
                'created_at' => '2025-12-10 22:32:13',
                'updated_at' => '2026-01-01 22:35:37',
            ],
        ];

        foreach ($data as $row) {
            DB::table('admin_users')->insert($row);
        }
    }
}
