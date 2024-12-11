<?php

namespace App\Jobs;

use App\Models\WhatsappLog;
use App\Services\WhatsappService\WebApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWhatsapp implements ShouldQueue
{
    use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

    protected $message;
    protected $number;
    protected $logId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message , $number , $logId)
    {
        $this->message = $message;
        $this->number = $number;
        $this->logId = $logId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $whatsappLog = WhatsappLog::with('whatsappGateway')->find(trim($this->logId));

        if (!$whatsappLog) {
            return;
        }


        //send api
        $messageId = null;
        try {

            $file = $whatsappLog->document ?? $whatsappLog->audio ?? $whatsappLog->image ?? $whatsappLog->video;

            if ($file) {
                $messageId = WebApiService::send(
                    $whatsappLog->whatsappGateway ,
                    $whatsappLog->to . '@c.us' ,
                    $file ,
                    'MessageMediaFromURL'
                );
            }

            if ($this->message) {
                $messageId = WebApiService::send(
                    $whatsappLog->whatsappGateway ,
                    $whatsappLog->to . '@c.us' ,
                    $this->message
                );
            }

            if ($messageId) {
                $whatsappLog->status = WhatsappLog::SUCCESS;
                $whatsappLog->web_message_id = $messageId;

                if ($whatsappLog->user && $whatsappLog->user->auto_delete_whatsapp_pc_messages) {
                    if (WebApiService::deleteMessage($whatsappLog->whatsappGateway , $messageId , $whatsappLog->to . '@c.us')) {
                        {
                            $whatsappLog->auto_deleted_at = now();
                        }
                    }
                }
            } else {
                $whatsappLog->status = WhatsappLog::FAILED;
                $whatsappLog->response_gateway = 'Error::2 Failed to send the message.';
            }

            $whatsappLog->save();

        } catch (Exception $exception) {
            $whatsappLog->status = WhatsappLog::FAILED;
            $whatsappLog->response_gateway = 'Something went wrong.';
            $whatsappLog->save();

            logger()->debug($exception->getMessage());
            logger()->debug($exception->getTraceAsString());
        }
    }
}
