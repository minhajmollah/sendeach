<?php

namespace App\Console\Commands;

use App\Models\WhatsappLog;
use Illuminate\Console\Command;

class CheckProcessingDesktopMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-processing:desktop-messages {minutes=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Long running processing whatsapp desktop messages back to pending';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        WhatsappLog::byDesktop(status: WhatsappLog::PROCESSING)
            ->where('updated_at', '<', now()->subMinutes($this->argument('minutes')))
            ->update(['status' => WhatsappLog::PENDING]);

        return Command::SUCCESS;
    }
}
