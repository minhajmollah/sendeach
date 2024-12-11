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
        Schema::table('wa_device', function (Blueprint $table) {
            $table->string('session_id')->nullable();
            $table->text('qr')->nullable();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wa_device', function (Blueprint $table) {
            $table->dropColumn('session_id');
            $table->dropColumn('qr');
            $table->dropColumn('data');
        });
    }
};
