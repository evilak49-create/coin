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
        Schema::table('action_records', function (Blueprint $table) {
            // device_number 字段经常用于查询，添加索引优化查询性能
            $table->index('device_number', 'idx_action_records_device_number');
            
            // app_package 字段用于查询和分组，添加索引优化查询性能
            $table->index('app_package', 'idx_action_records_app_package');
            
            // operate_date 字段用于按日期筛选，添加索引优化查询性能
            $table->index('operate_date', 'idx_action_records_operate_date');
            
            // action 字段用于按动作筛选，添加索引优化查询性能
            $table->index('action', 'idx_action_records_action');
            
            // user_action 字段用于按用户动作筛选，添加索引优化查询性能
            $table->index('user_action', 'idx_action_records_user_action');
            
            // device_number、app_package、action 经常一起查询（用于查询上一条记录），添加复合索引
            $table->index(['device_number', 'app_package', 'action'], 'idx_action_records_device_package_action');
            
            // operate_date 和 app_package 经常一起查询（用于分析统计），添加复合索引
            $table->index(['operate_date', 'app_package'], 'idx_action_records_date_package');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_records', function (Blueprint $table) {
            $table->dropIndex('idx_action_records_device_number');
            $table->dropIndex('idx_action_records_app_package');
            $table->dropIndex('idx_action_records_operate_date');
            $table->dropIndex('idx_action_records_action');
            $table->dropIndex('idx_action_records_user_action');
            $table->dropIndex('idx_action_records_device_package_action');
            $table->dropIndex('idx_action_records_date_package');
        });
    }
};

