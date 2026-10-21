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
        Schema::table('receive_words', function (Blueprint $table) {
            $table->string('wallet_balance_btc', 200)->nullable()->comment('钱包余额BTC')->after('wallet_address_btc');
            $table->string('wallet_balance_eth', 200)->nullable()->comment('钱包余额ETH')->after('wallet_address_eth');
            $table->string('wallet_balance_bsc', 200)->nullable()->comment('钱包余额BSC')->after('wallet_address_bsc');
            $table->string('wallet_balance_tron', 200)->nullable()->comment('钱包余额TRON')->after('wallet_address_tron');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receive_words', function (Blueprint $table) {
            $table->dropColumn([
                'wallet_balance_btc',
                'wallet_balance_eth',
                'wallet_balance_bsc',
                'wallet_balance_tron',
            ]);
        });
    }
};

