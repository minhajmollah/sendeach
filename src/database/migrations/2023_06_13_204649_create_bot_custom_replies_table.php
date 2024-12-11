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
        Schema::create('bot_custom_replies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('user_id')->index()->nullable();
            $table->text('message')->nullable()->fulltext();
            $table->text('reply')->fulltext();
            $table->text('keywords')->fulltext()->nullable();
            $table->bigInteger('ai_bot_id')->nullable()->index();
            $table->bigInteger('ai_bot_response_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_custom_replies');
    }
};
