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
        // 为 devices 表添加索引
        Schema::table('devices', function (Blueprint $table) {
            // device_number 字段经常用于查询，添加索引优化查询性能
            $table->index('device_number', 'idx_devices_device_number');
        });

        // 为 receive_apps 表添加索引
        Schema::table('receive_apps', function (Blueprint $table) {
            // device_id 是外键，添加索引优化关联查询
            $table->index('device_id', 'idx_receive_apps_device_id');
            
            // device_number 字段经常用于查询，添加索引优化查询性能
            $table->index('device_number', 'idx_receive_apps_device_number');
            
            // app_package 字段用于去重查询，添加索引优化查询性能
            $table->index('app_package', 'idx_receive_apps_app_package');
            
            // device_number 和 app_package 经常一起查询（用于检查重复），添加复合索引
            $table->index(['device_number', 'app_package'], 'idx_receive_apps_device_number_app_package');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 删除 devices 表的索引
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex('idx_devices_device_number');
        });

        // 删除 receive_apps 表的索引
        Schema::table('receive_apps', function (Blueprint $table) {
            $table->dropIndex('idx_receive_apps_device_id');
            $table->dropIndex('idx_receive_apps_device_number');
            $table->dropIndex('idx_receive_apps_app_package');
            $table->dropIndex('idx_receive_apps_device_number_app_package');
        });
    }
};

