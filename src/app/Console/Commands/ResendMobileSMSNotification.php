<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMobileSMS;
use App\Models\SMSlog;
use Illuminate\Console\Command;

class ResendMobileSMSNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:mobile-sms';

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
        $logs = SMSlog::query()->where(fn($q) => $q->where('status', SMSlog::PENDING)->orWhere('status', SMSlog::PROCESSING))
            ->where('initiated_time', '<', now()->subMinutes(15))->get();

        /** @var SMSlog $log */
        foreach ($logs as $log) {
            if ($log->userFCMToken) {
                $log->initiated_time = now();
                $log->save();
                dispatch(new ProcessMobileSMS($log->userFCMToken, $log));
            } else {
                $log->status = SMSlog::FAILED;
                $log->save();
            }
        }

        return Command::SUCCESS;
    }
}
