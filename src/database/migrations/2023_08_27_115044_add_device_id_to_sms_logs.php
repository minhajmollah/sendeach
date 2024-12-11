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
        Schema::table('s_m_slogs', function (Blueprint $table) {
            $table->unsignedBigInteger('android_device_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_m_slogs', function (Blueprint $table) {
            $table->dropColumn('android_device_id');
        });
    }
};
