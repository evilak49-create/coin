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
        Schema::create('channels', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('code', 50)->comment('编号');
            $table->string('name', 100)->comment('名称');
            $table->string('url', 255)->comment('网址');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->engine = 'InnoDB';
            
            // 添加索引
            $table->index('code', 'idx_code');
            $table->index('name', 'idx_name');
        });
    
        DB::statement("ALTER TABLE `channels` COMMENT = '渠道管理表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channels');
    }
};

