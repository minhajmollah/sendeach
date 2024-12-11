<?php

namespace App\Jobs;

use App\Http\Utility\SendSMS;
use App\Models\CreditLog;
use App\Models\SMSlog;
use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMobileSMS implements ShouldQueue
{
    use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public UserFcmToken $device, public $log)
    {
    }

    public function handle(): void
    {
        if(!$this->device) return;

        try {
            $arr = [
                'to' => $this->device->token,
                'data' => [
                    'id' => $this->log->id,
                    'recipients' =>  explode(",", $this->log->to),
                    'message' => $this->log->message
                ]
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($arr),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: key=AAAABNs4OMs:APA91bHuNuon4-_avC87wr9i52a-YCzEu5hTAjHpAVfHVImsFf35a76txNfplEYv89_AezstftI-uwRFKdnAUwXPglCZh2UOFc5x2UHpcCQadc4xru1ni0w1eb1J0r3oYZDV4I_arzJX'
                ),
            ));

            $result = json_decode(curl_exec($curl));

            if ($result->success != 1) {
                $this->log->status = SMSlog::FAILED;
                $this->log->response_gateway = json_encode($result);
                $this->log->save();

            } else {
                $this->log->status = SMSlog::PENDING;
                $this->log->response_gateway = json_encode($result);
                $this->log->save();

            }

        } catch (\Throwable $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
        }
    }
}
