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
        Schema::create('facebook_messenger_senders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('psid');
            $table->foreignId('facebook_messenger_id')->constrained('facebook_messengers')->onDelete('CASCADE');
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
        Schema::dropIfExists('facebook_messenger_senders');
    }
};
