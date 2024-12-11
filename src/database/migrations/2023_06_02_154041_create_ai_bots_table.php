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
        Schema::create('ai_bots', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('model');
            $table->boolean('is_default_use')->default(false);
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('total_tokens_used')->default(0);
            $table->integer('max_tokens')->default(50);
            $table->integer('temperature')->default(1);
            $table->integer('n')->default(1);
            $table->integer('messages_per_minute')->default(60);
            $table->json('data')->nullable();
            $table->float('price_per_1000_tokens', 10, 8)->default(0.001);
            $table->bigInteger('charged_tokens')->default(0);
            $table->boolean('is_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_bots');
    }
};
