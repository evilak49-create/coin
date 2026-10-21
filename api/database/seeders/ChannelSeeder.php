<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'code' => 'aaa',
                'name' => '渠道A',
                'url' => 'https://sss.com',
                'remark' => '34343lll',
                'created_at' => '2025-12-05 16:28:53',
                'updated_at' => '2025-12-05 16:29:37',
            ],
            [
                'id' => 2,
                'code' => 'hgjug',
                'name' => '356546547654',
                'url' => 'http://sgsdgsd11',
                'remark' => 'gsdgsdgf',
                'created_at' => '2025-12-05 16:30:14',
                'updated_at' => '2025-12-05 16:30:34',
            ],
            [
                'id' => 3,
                'code' => 'google_play',
                'name' => '谷歌',
                'url' => 'https://www.google.com',
                'remark' => '谷歌网址',
                'created_at' => '2025-12-09 23:10:03',
                'updated_at' => '2025-12-09 23:10:03',
            ],
            [
                'id' => 4,
                'code' => 'SelectAI',
                'name' => 'SelectAI',
                'url' => 'https://www.selectai.io',
                'remark' => null,
                'created_at' => '2025-12-11 11:24:42',
                'updated_at' => '2025-12-11 11:24:42',
            ],
            [
                'id' => 5,
                'code' => '5AFund',
                'name' => '5AFund',
                'url' => 'https://hibtc.cc/',
                'remark' => '5AFund',
                'created_at' => '2025-12-29 19:53:11',
                'updated_at' => '2025-12-30 20:37:21',
            ],
        ];

        foreach ($data as $row) {
            DB::table('channels')->insert($row);
        }
    }
}
