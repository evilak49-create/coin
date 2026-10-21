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
        Schema::create('receive_sms', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('device_id')->nullable()->comment('设备ID');
            $table->string('device_number', 100)->nullable()->comment('设备号');
            $table->string('sender', 100)->nullable()->comment('发送方');
            $table->text('content')->nullable()->comment('短信内容');
            $table->timestamp('sms_time')->nullable()->comment('短信时间');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    
        DB::statement("ALTER TABLE `receive_sms` COMMENT = '收信短信表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receive_sms');
    }
};

