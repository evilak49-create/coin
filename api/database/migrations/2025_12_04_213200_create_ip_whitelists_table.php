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
        Schema::create('ip_whitelists', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('ip', 50)->comment('IP地址或CIDR格式（如：192.168.1.1 或 192.168.1.0/24）');
            $table->string('type', 20)->default('whitelist')->comment('类型: whitelist-白名单, blacklist-黑名单');
            $table->string('description', 255)->nullable()->comment('描述说明');
            $table->tinyInteger('status')->default(1)->comment('状态: 0-禁用, 1-启用');
            $table->timestamps();
            $table->engine = 'InnoDB';
            
            // 添加索引
            $table->index('ip', 'idx_ip');
            $table->index('type', 'idx_type');
            $table->index('status', 'idx_status');
        });
    
        DB::statement("ALTER TABLE `ip_whitelists` COMMENT = 'IP白名单表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_whitelists');
    }
};

