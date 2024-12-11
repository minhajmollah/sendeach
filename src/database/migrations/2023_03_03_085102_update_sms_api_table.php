<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSmsApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('android_apis', function (Blueprint $table) {
            if(!Schema::hasColumn('android_apis', 'user_id')){
                $table->string('user_id', 100)->nullable()->after('status');
            }
            
            if(!Schema::hasColumn('android_apis', 'user_type')){
                $table->string('user_type', 100)->nullable()->after('user_id');
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
        Schema::table('android_apis', function (Blueprint $table) {
            if(Schema::hasColumn('android_apis', 'user_id')){
                $table->dropColumn('user_id');
            }
            
            if(Schema::hasColumn('android_apis', 'user_type')){
                $table->dropColumn('user_type');
            }
        });
    }
}
