<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `receive_sms` MODIFY COLUMN `sms_time` VARCHAR(100) NULL COMMENT \'短信时间\'');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `receive_sms` MODIFY COLUMN `sms_time` TIMESTAMP NULL COMMENT \'短信时间\'');
    }
};

