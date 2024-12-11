<?php

use App\Models\WhatsappAccessToken;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPinToWhatsappPhoneNumbers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_phone_numbers', function (Blueprint $table) {
            $table->string('pin')->nullable();
            $table->boolean('is_registered')->nullable();
            $table->string('type')->default(WhatsappAccessToken::TYPE_OWN);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_phone_numbers', function (Blueprint $table) {
            $table->dropColumn(['pin', 'is_registered', 'type']);
        });
    }
}
