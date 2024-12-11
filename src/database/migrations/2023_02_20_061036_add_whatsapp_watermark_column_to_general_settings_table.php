<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappWatermarkColumnToGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if(!Schema::hasColumn('general_settings', 'free_watermark')){
                $table->string('free_watermark', 255)->nullable()->after('site_name');
            }
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
            if(Schema::hasColumn('general_settings', 'free_watermark')){
                $table->dropColumn('free_watermark');
            }
        });
    }
}
