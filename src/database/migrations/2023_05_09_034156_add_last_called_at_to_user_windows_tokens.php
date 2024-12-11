<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastCalledAtToUserWindowsTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_windows_tokens', function (Blueprint $table) {
            $table->timestamp('last_called_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_windows_tokens', function (Blueprint $table) {
            $table->dropColumn('last_called_at');
        });
    }
}
