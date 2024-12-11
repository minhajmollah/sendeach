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
            $table->string('batch_id')->nullable();
        });

        Schema::table('s_m_slogs' , function (Blueprint $table) {
            $table->string('batch_id')->nullable();
        });

        Schema::table('email_logs' , function (Blueprint $table) {
            $table->string('batch_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_logs' , function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });

        Schema::table('s_m_slogs' , function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });

        Schema::table('email_logs' , function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};
