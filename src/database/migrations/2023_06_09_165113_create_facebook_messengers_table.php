<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_messengers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('challenge')->nullable();
            $table->string('page_id')->nullable()->index();
            $table->string('page_access_token')->nullable();
            $table->json('data')->nullable();
            $table->smallInteger('status')->default(0);
            $table->bigInteger('facebook_login_id')->nullable()->index();
            $table->bigInteger('ai_bot_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facebook_messengers');
    }
};
