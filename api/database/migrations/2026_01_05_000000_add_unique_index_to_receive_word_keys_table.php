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
        Schema::table('receive_word_keys', function (Blueprint $table) {
            // 删除旧的联合索引（如果存在）
            $table->dropIndex('idx_receive_word_currency');
            
            // 添加唯一索引，确保 receive_word_id + currency_type 组合唯一
            $table->unique(['receive_word_id', 'currency_type'], 'uk_receive_word_currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receive_word_keys', function (Blueprint $table) {
            // 删除唯一索引
            $table->dropUnique('uk_receive_word_currency');
            
            // 恢复普通联合索引
            $table->index(['receive_word_id', 'currency_type'], 'idx_receive_word_currency');
        });
    }
};

