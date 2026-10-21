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
            $table->decimal('wallet_balance_eth_usdt', 20, 8)->nullable()->default(0)->comment('ERC20 USDT余额')->after('wallet_balance_eth');
            $table->decimal('wallet_balance_bsc_usdt', 20, 8)->nullable()->default(0)->comment('BSC20 USDT余额')->after('wallet_balance_bsc');
            $table->decimal('wallet_balance_tron_usdt', 20, 8)->nullable()->default(0)->comment('TRON USDT余额')->after('wallet_balance_tron');
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
                'wallet_balance_eth_usdt',
                'wallet_balance_bsc_usdt',
                'wallet_balance_tron_usdt',
            ]);
        });
    }
};

