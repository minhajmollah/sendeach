<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappPcMessageDeletesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_pc_message_deletes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('keywords')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('status')->nullable();
            $table->integer('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_pc_message_deletes');
    }
}
