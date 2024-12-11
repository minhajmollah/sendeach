<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToWaDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wa_device', function (Blueprint $table) {
            $table->string('user_type')->default('admin')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wa_device', function (Blueprint $table) {
            if(Schema::hasColumn('wa_device', 'user_type')){
                $table->dropColumn('user_type');
            }
        });
    }
}
