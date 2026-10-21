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
        // 先删除字段
        Schema::table('action_records', function (Blueprint $table) {
            $table->dropColumn(['popup_time', 'operate_time']);
        });
        
        // 再添加新字段
        Schema::table('action_records', function (Blueprint $table) {
            $table->date('operate_date')->nullable()->comment('操作日期')->after('user_action');
        });
    
        DB::statement("ALTER TABLE `action_records` COMMENT = '动作记录表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 先删除操作日期字段
        Schema::table('action_records', function (Blueprint $table) {
            $table->dropColumn('operate_date');
        });
        
        // 再恢复 popup_time 和 operate_time 字段
        Schema::table('action_records', function (Blueprint $table) {
            $table->timestamp('popup_time')->nullable()->comment('弹出时间')->after('user_action');
            $table->timestamp('operate_time')->nullable()->comment('操作时间')->after('popup_time');
        });
    }
};

