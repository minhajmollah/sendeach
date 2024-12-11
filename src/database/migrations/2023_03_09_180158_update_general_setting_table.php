<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGeneralSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if(!Schema::hasColumn('general_settings', 'user_id')){
                $table->string('user_id', 100)->nullable()->after('phone')->default(1);
            }
            
             if(!Schema::hasColumn('general_settings', 'user_type')){
                $table->string('user_type', 100)->nullable()->after('user_id')->default('admin');
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
            if(Schema::hasColumn('general_settings', 'user_id')){
                $table->dropColumn('user_id');
            }
            
            if(Schema::hasColumn('general_settings', 'user_type')){
                $table->dropColumn('user_type');
            }
        });
    }
}
