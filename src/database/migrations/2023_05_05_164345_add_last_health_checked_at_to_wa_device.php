<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastHealthCheckedAtToWaDevice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wa_device' , function (Blueprint $table) {
            $table->timestamp('last_health_checked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wa_device' , function (Blueprint $table) {
            $table->dropColumn(['last_health_checked_at']);
        });
    }
}
