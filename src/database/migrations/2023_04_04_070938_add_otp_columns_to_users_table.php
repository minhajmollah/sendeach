<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtpColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('otp_time')->nullable()->after('status');
            $table->string('otp', 8)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if(Schema::hasColumn('users', 'otp_time')){
                $table->dropColumn('otp_time');
            }
            if(Schema::hasColumn('users', 'otp')){
                $table->dropColumn('otp');
            }
        });
    }
}
