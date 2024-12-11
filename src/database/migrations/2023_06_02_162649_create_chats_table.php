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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('message');
            $table->string('type')->default('text');
            $table->string('status');
            $table->boolean('is_sender');
            $table->foreignId('conversation_id')->constrained('chat_conversations', 'id')->onDelete('CASCADE');
            $table->json('data')->nullable();
            $table->string('messenger_message_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
