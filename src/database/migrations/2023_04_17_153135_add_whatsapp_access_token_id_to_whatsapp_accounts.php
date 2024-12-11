<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappAccessTokenIdToWhatsappAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_accounts' , function (Blueprint $table) {
            $table->string('whatsapp_access_token_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_accounts' , function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_accounts' , 'whatsapp_access_token_id')) {
                $table->dropColumn('whatsapp_access_token_id');
            }
        });
    }
}
