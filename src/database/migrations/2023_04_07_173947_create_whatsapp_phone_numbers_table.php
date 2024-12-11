<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappPhoneNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('display_phone_number')->unique();
            $table->string('whatsapp_phone_number_id')->unique();
            $table->string('verified_name');
            $table->string('code_verification_status');
            $table->string('quality_rating');
            $table->string('whatsapp_business_id');
            $table->string('user_id')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_phone_numbers');
    }
}
