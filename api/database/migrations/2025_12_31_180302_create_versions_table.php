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
        Schema::create('versions', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('version_code', 50)->comment('版本号（如：1.0.0）');
            $table->integer('version_number')->comment('版本数字（用于比较，如：100 表示 1.0.0）');
            $table->string('platform', 20)->default('android')->comment('平台：android/ios');
            $table->string('download_url', 500)->nullable()->comment('下载地址');
            $table->text('update_log')->nullable()->comment('更新日志');
            $table->tinyInteger('force_update')->default(0)->comment('是否强制更新：0否 1是');
            $table->tinyInteger('status')->default(1)->comment('状态：0禁用 1启用');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    
        DB::statement("ALTER TABLE `versions` COMMENT = '版本管理表'");
        
        // 添加索引
        Schema::table('versions', function (Blueprint $table) {
            $table->index(['platform', 'status'], 'idx_platform_status');
            $table->index('version_number', 'idx_version_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('versions');
    }
};

