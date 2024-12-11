<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessLogsToWhatsappLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->string('whatsapp_business_id')->nullable();
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->string('whatsapp_template_id')->nullable();
            $table->string('gateway')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_business_id', 'whatsapp_template_id', 'whatsapp_phone_number_id', 'gateway']);
        });
    }
}
