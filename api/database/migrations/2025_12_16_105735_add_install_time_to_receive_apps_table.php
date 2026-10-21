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
        Schema::table('receive_apps', function (Blueprint $table) {
            $table->string('install_time', 100)->nullable()->comment('安装时间')->after('app_package');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receive_apps', function (Blueprint $table) {
            $table->dropColumn('install_time');
        });
    }
};
