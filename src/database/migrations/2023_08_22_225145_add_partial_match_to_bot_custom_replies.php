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
            $table->boolean('is_partial_match')->default(false);
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
            $table->dropColumn('is_partial_match');
        });
    }
};
