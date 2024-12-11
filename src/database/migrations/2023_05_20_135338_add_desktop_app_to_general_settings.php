<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDesktopAppToGeneralSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->unsignedDecimal('desktop_app_version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('desktop_app_version');
        });
    }
}
