<?php

namespace App\Console\Commands;

use App\Services\WhatsappService\WebApiService;
use Illuminate\Console\Command;

class FlushInActiveWhatsappWebSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flush:inactive-web-whatsapp-session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return WebApiService::terminateInActive() ? Command::SUCCESS : Command::FAILURE;
    }
}
