<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSmsApiGatewayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_gateways', function (Blueprint $table) {
            if(!Schema::hasColumn('sms_gateways', 'user_id')){
                $table->string('user_id', 100)->nullable()->after('status');
            }
            
            if(!Schema::hasColumn('sms_gateways', 'user_type')){
                $table->string('user_type', 100)->nullable()->after('user_id')->default('admin');
            }
            
            if(!Schema::hasColumn('sms_gateways', 'default_use')){
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
        Schema::table('sms_gateways', function (Blueprint $table) {
            if(Schema::hasColumn('sms_gateways', 'user_id')){
                $table->dropColumn('user_id');
            }
            
            if(Schema::hasColumn('sms_gateways', 'user_type')){
                $table->dropColumn('user_type');
            }
            
            if(Schema::hasColumn('sms_gateways', 'default_use')){
                $table->dropColumn('default_use');
            }
        });
    }
}
