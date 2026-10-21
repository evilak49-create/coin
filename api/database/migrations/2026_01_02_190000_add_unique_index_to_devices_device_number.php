<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            // 先删除普通索引
            $table->dropIndex('idx_devices_device_number');
            // 添加唯一索引
            $table->unique('device_number', 'idx_devices_device_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            // 删除唯一索引
            $table->dropUnique('idx_devices_device_number_unique');
            // 恢复普通索引
            $table->index('device_number', 'idx_devices_device_number');
        });
    }
};

