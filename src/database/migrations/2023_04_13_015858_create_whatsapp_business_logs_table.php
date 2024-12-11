<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappBusinessLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_business_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('whatsapp_business_id');
            $table->string('whatsapp_phone_number_id');
            $table->string('to');
            $table->string('whatsapp_template_id')->nullable();
            $table->string('initiated_time')->nullable();
            $table->string('schedule_status')->nullable();
            $table->string('status')->nullable();
            $table->string('response_gateway')->nullable();
            $table->string('user_id')->nullable();
            $table->string('message')->nullable();
            $table->string('document')->nullable();
            $table->string('video')->nullable();
            $table->string('audio')->nullable();
            $table->string('image')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_business_logs');
    }
}
