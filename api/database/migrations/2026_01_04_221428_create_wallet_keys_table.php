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
        Schema::create('receive_word_keys', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('receive_word_id')->comment('助记词ID，关联receive_words表');
            $table->string('currency_type', 50)->comment('币种类型，如BTC、ETH、BSC、TRON、SOL、TON、XRP等');
            $table->string('address', 255)->comment('钱包地址');
            $table->text('private_key')->nullable()->comment('私钥（加密存储）');
            $table->decimal('balance', 20, 8)->default(0)->comment('余额');
            $table->decimal('total_sent', 20, 8)->default(0)->comment('转出金额');
            $table->timestamp('balance_updated_at')->nullable()->comment('余额更新时间');
            $table->timestamps();
            
            // 添加索引
            $table->index('receive_word_id', 'idx_receive_word_id');
            $table->index('currency_type', 'idx_currency_type');
            $table->index('address', 'idx_address');
            $table->index(['receive_word_id', 'currency_type'], 'idx_receive_word_currency');
            
            $table->engine = 'InnoDB';
        });
    
        DB::statement("ALTER TABLE `receive_word_keys` COMMENT = '钱包密钥表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receive_word_keys');
    }
};

