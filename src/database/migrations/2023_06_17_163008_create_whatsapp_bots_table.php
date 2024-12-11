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
        Schema::create('whatsapp_bots' , function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('whatsapp_gateway_id')->nullable()->index();
            $table->bigInteger('user_id')->nullable()->index();
            $table->foreignId('ai_bot_id')->nullable()->constrained('ai_bots' , 'id');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('handle_only_unknown_user')->default(true);
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
        Schema::dropIfExists('whatsapp_bots');
    }
};
