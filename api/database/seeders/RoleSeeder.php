<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'name' => '超级管理员',
                'slug' => 'admin',
                'is_admin' => 1,
                'created_at' => '2024-10-24 18:29:09',
                'updated_at' => '2024-10-24 18:29:09',
            ],
        ];

        foreach ($data as $row) {
            DB::table('roles')->insert($row);
        }
    }
}
