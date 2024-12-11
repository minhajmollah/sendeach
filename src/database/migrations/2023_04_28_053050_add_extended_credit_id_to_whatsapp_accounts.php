<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtendedCreditIdToWhatsappAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->string('extended_credit_id')->nullable();
            $table->string('allocation_config_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn(['extended_credit_id', 'allocation_config_id']);
        });
    }
}
