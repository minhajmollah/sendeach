<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;

class CheckAndAddDefaultLimitColumn extends Command
{
    protected $signature = 'db:add-default-limit-column';

    protected $description = 'Add default_limit column to the users table';

    public function handle()
    {
        if (!Schema::hasColumn('users', 'default_limit')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('default_limit')->default(200);
            });

            $this->info('Column "default_limit" added to users table.');
        } else {
            $this->info('Column "default_limit" already exists in users table.');
        }
    }
}