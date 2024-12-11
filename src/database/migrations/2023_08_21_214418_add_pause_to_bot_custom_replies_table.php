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
        Schema::table('bot_custom_replies', function (Blueprint $table) {
            $table->boolean('to_pause')->nullable();
            $table->boolean('pause_duration')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bot_custom_replies', function (Blueprint $table) {
            $table->dropColumn('to_pause');
            $table->dropColumn('pause_duration');
        });
    }
};
