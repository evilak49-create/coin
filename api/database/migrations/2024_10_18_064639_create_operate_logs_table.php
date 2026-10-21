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
        Schema::create('operate_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id')->default(0)->comment('操作用户ID');
            $table->string('path', 255)->comment('路由');
            $table->string('methods', 10)->comment('请求方式');
            $table->text('input')->comment('内容');
            $table->string('remark', 200)->nullable()->comment('说明');
            $table->string('ip', 20)->comment('ip地址');
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
        Schema::dropIfExists('operate_logs');
    }
};
