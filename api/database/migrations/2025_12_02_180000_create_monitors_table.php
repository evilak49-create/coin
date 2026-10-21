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
        Schema::create('monitors', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('app_name', 100)->comment('APP名称');
            $table->string('app_package', 200)->comment('APP包名');
            $table->tinyInteger('type')->default(1)->comment('类型 1助记词 2信用卡');
            $table->tinyInteger('is_enabled')->default(1)->comment('是否开启监听 0关闭 1开启');
            $table->timestamp('deleted_at')->nullable()->comment('软删除时间');
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
    
        DB::statement("ALTER TABLE `monitors` COMMENT = '监听配置表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitors');
    }
};

