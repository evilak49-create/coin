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
        Schema::table('versions', function (Blueprint $table) {
            $table->string('channel_code', 50)->nullable()->default('')->comment('渠道编号')->after('platform');
            // 添加索引，用于根据平台和渠道查询版本
            $table->index(['platform', 'channel_code', 'status'], 'idx_platform_channel_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->dropIndex('idx_platform_channel_status');
            $table->dropColumn('channel_code');
        });
    }
};

