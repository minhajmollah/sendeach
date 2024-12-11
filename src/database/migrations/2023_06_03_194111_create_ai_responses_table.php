<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_bot_responses' , function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('bot_id')->constrained('ai_bots' , 'id')->onDelete('CASCADE');
            $table->integer('total_tokens_used');
            $table->integer('temperature')->default(1);
            $table->json('choices');
            $table->text('message');
            $table->text('feedback')->nullable();
            $table->integer('likes')->nullable();
            $table->integer('dis_likes')->nullable();
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
        Schema::dropIfExists('ai_responses');
    }
};
