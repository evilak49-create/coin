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
        Schema::table('devices', function (Blueprint $table) {
            // 为app_channel字段添加索引（用于渠道关联查询优化）
            $table->index('app_channel', 'idx_devices_app_channel');
            
            // 为app_channel和deleted_at添加联合索引（用于带软删除的查询优化）
            $table->index(['app_channel', 'deleted_at'], 'idx_devices_app_channel_deleted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex('idx_devices_app_channel');
            $table->dropIndex('idx_devices_app_channel_deleted');
        });
    }
};

