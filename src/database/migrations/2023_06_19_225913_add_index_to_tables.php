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
        Schema::table('whatsapp_logs' , function (Blueprint $table) {
            $table->index('user_id');
            $table->index('whatsapp_template_id');
            $table->index('whatsapp_id');
            $table->index('whatsapp_business_id');
            $table->index('whatsapp_phone_number_id');
//            $table->fullText('message');
        });

//        Schema::table('chats' , function (Blueprint $table) {
//            $table->fullText('message');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_logs' , function (Blueprint $table) {
            $table->dropIndex('whatsapp_logs_user_id_index');
            $table->dropIndex('whatsapp_logs_whatsapp_id_index');
            $table->dropIndex('whatsapp_logs_whatsapp_template_id_index');
            $table->dropIndex('whatsapp_logs_whatsapp_business_id_index');
            $table->dropIndex('whatsapp_logs_whatsapp_phone_number_id_index');
//            $table->dropFullText('whatsapp_logs_message_fulltext');
        });

//        Schema::table('chats' , function (Blueprint $table) {
//            $table->dropFullText('chats_message_fulltext');
//        });
    }
};
