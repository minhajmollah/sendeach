<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_logins', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('whatsapp_access_token_id')->nullable();
            $table->string('accessToken')->nullable();
            $table->string('userID')->nullable()->index();
            $table->bigInteger('user_id')->nullable()->index();
            $table->text('signedRequest')->nullable();
            $table->string('graphDomain')->nullable();
            $table->string('expiresIn')->nullable();
            $table->string('data_access_expiration_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facebook_logins');
    }
}
