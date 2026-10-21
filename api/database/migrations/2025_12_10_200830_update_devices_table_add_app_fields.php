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
        Schema::table('devices', function (Blueprint $table) {
            // 删除version_type字段
            $table->dropColumn('version_type');
            
            // 添加appName字段
            $table->string('appName', 200)->nullable()->comment('APP名称')->after('device_number');
            
            // 添加appVersion字段
            $table->string('appVersion', 100)->nullable()->comment('APP版本')->after('appName');
        });
    
        DB::statement("ALTER TABLE `devices` COMMENT = '设备信息表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            // 恢复version_type字段
            $table->string('version_type', 50)->nullable()->comment('版本类型')->after('device_number');
            
            // 删除新增的字段
            $table->dropColumn(['appName', 'appVersion']);
        });
    }
};

