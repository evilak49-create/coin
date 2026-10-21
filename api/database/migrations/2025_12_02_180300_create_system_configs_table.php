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
        Schema::create('system_configs', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('key', 100)->unique()->comment('配置键名');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('type', 20)->default('string')->comment('配置类型: string-字符串, number-数字, image-图片');
            $table->string('group', 50)->nullable()->default('default')->comment('配置分组');
            $table->string('description', 255)->nullable()->comment('配置描述');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态: 0-禁用, 1-启用');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    
        DB::statement("ALTER TABLE `system_configs` COMMENT = '系统配置表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_configs');
    }
};

