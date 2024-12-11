<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoDeletedAtColumnToWhatsappLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->timestamp('auto_deleted_at')->nullable();
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
            $table->dropColumn('auto_deleted_at');
        });
    }
}
