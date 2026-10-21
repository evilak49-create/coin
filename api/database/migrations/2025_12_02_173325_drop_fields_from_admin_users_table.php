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
        Schema::table('admin_users', function (Blueprint $table) {
            // 删除字段
            $table->dropColumn([
                'phone',
                'openid',
                'name',
                'is_reset',
                'is_information',
                'unit_id',
                'type',
                'receive_sms'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            // 恢复字段
            $table->char('phone', 11)->nullable()->default('')->comment('手机号')->after('username');
            $table->string('openid', 50)->nullable()->default('')->comment('微信openid')->after('phone');
            $table->string('name', 50)->nullable()->default('')->comment('名字')->after('password');
            $table->tinyInteger('is_reset')->default(0)->comment('是否重置账号 0否 1是')->after('status');
            $table->tinyInteger('is_information')->default(0)->comment('是否完善资料(代理机构) 0否 1是')->after('is_reset');
            $table->unsignedBigInteger('unit_id')->default(0)->comment('单位ID')->after('role_ids');
            $table->tinyInteger('type')->default(0)->comment('账号类型 1超管 2代理机构 3集团')->after('unit_id');
            $table->tinyInteger('receive_sms')->default(0)->comment('接收短信')->after('type');
        });
    }
};
