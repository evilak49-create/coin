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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pid')->default(0)->comment('父级id');
            $table->string('title', 50)->comment('权限名称');
            $table->string('redirect', 100)->nullable()->comment('重定向');
            $table->string('route', 100)->nullable()->comment('后端路由');
            $table->string('name', 50)->nullable()->comment('路由名称');
            $table->string('path', 100)->nullable()->comment('前端路由');
            $table->string('component', 100)->nullable()->comment('前端组件');
            $table->string('icon', 100)->nullable()->comment('icon');
            $table->boolean('is_hide')->default(false)->comment('是否隐藏 true/false');
            $table->boolean('is_full')->default(false)->comment('是否全屏 true/false');
            $table->boolean('is_affix')->default(false)->comment('是否粘贴 true/false');
            $table->bigInteger('sort')->default(0)->comment('排序');
            $table->tinyInteger('type')->default(1)->comment('类型 1菜单 2按钮');
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
        Schema::dropIfExists('permissions');
    }
};
