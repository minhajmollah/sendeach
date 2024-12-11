<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_templates' , function (Blueprint $table) {
            $table->id();
            $table->string('whatsapp_template_id')->nullable()->unique();
            $table->integer('user_id')->nullable();
            $table->timestamps();
            $table->string('name');
            $table->string('status');
            $table->string('category');
            $table->string('language');
            $table->text('rejected_reason');
            $table->json('components');
            $table->string('whatsapp_business_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_templates');
    }
}
