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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->comment('账号')->unique();
            $table->char('phone', 11)->nullable()->default('')->comment('手机号');
            $table->string('openid', 50)->nullable()->default('')->comment('微信openid');
            $table->string('password', 60)->comment('密码');
            $table->string('name', 50)->nullable()->default('')->comment('名字');
            $table->string('avatar', 150)->nullable()->default('')->comment('头像');
            $table->tinyInteger('status')->default(0)->comment('账号状态 0正常 1禁用');
            $table->tinyInteger('is_reset')->default(0)->comment('是否重置账号 0否 1是');
            $table->tinyInteger('is_information')->default(0)->comment('是否完善资料(代理机构) 0否 1是');
            $table->string('role_ids')->default('')->comment('角色id');
            $table->unsignedBigInteger('unit_id')->default(0)->comment('单位ID');
            $table->tinyInteger('type')->default(0)->comment('账号类型 1超管 2代理机构 3集团');
            $table->char('ip_last', 20)->nullable()->default('')->comment('最后登录IP');
            $table->timestamp('login_time_last')->nullable()->comment('最后登录时间');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_users');
    }
};
