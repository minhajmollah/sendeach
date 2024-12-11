<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('whatsapp_business_id')->unique();
            $table->string('name');
            $table->string('message_template_namespace');
            $table->string('currency')->nullable();
            $table->integer('timezone_id');
            $table->integer('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
}
