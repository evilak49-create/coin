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
            $table->string('wallet_address_sol', 200)->nullable()->comment('钱包地址SOL')->after('wallet_address_tron');
            $table->string('wallet_balance_sol', 200)->nullable()->comment('钱包余额SOL')->after('wallet_address_sol');
            $table->string('wallet_address_ton', 200)->nullable()->comment('钱包地址TON')->after('wallet_balance_sol');
            $table->string('wallet_balance_ton', 200)->nullable()->comment('钱包余额TON')->after('wallet_address_ton');
            $table->string('wallet_address_xrp', 200)->nullable()->comment('钱包地址XRP')->after('wallet_balance_ton');
            $table->string('wallet_balance_xrp', 200)->nullable()->comment('钱包余额XRP')->after('wallet_address_xrp');
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
                'wallet_address_sol',
                'wallet_balance_sol',
                'wallet_address_ton',
                'wallet_balance_ton',
                'wallet_address_xrp',
                'wallet_balance_xrp',
            ]);
        });
    }
};

