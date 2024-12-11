<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mails', function (Blueprint $table) {
            if(!Schema::hasColumn('mails', 'user_id')){
                $table->string('user_id', 100)->nullable()->after('driver_information');
            }
            
            if(!Schema::hasColumn('mails', 'user_type')){
                $table->string('user_type', 100)->nullable()->after('user_id')->default('admin');
            }
            
            if(!Schema::hasColumn('mails', 'default_use')){
                $table->boolean('default_use', 100)->nullable()->after('user_type')->default(0);
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
        Schema::table('mails', function (Blueprint $table) {
            if(Schema::hasColumn('mails', 'user_id')){
                $table->dropColumn('user_id');
            }
            
            if(Schema::hasColumn('mails', 'user_type')){
                $table->dropColumn('user_type');
            }
            
            if(Schema::hasColumn('mails', 'default_use')){
                $table->dropColumn('default_use');
            }
        });
    }
}
