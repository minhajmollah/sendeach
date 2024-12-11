<?php

namespace App\Jobs;

use App\Models\SMSlog;
use App\Models\User;
use App\Models\WhatsappLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Vonage\SMS\Message\SMS;

class ProcessBulkSMSViaAndroid extends ProcessBulkMobileMessages
{

    public function handle()
    {
        $this->user = User::query()->findOrFail($this->user);
        $this->setWatermark();
        $this->initiatedTime = $this->request['schedule_date'] ?? now();
        $isAntiBlockEnabled = Arr::get($this->user->data, 'sms.anti_block');

        $i = 0;
        $n = count($this->gateways);

        $recipients = collect($this->request['to'])->chunk(5)->toArray();

        foreach ($recipients as $to) {

            $message = $this->messages[$i % $this->nMessages];

            $gateway = $this->gateways->values()->get($i % $n);

            if ($isAntiBlockEnabled) {
                // Anti Block Strategy to delay the initiated time.
                $cacheKey = "user:: {$this->user->id}::sms-gateway::{$gateway->id}";
                WhatsappLog::delayInitiatedTime($cacheKey, $this->initiatedTime);
            }

            $i++;

            $this->send($gateway, $to, $message);
        }
    }

    public function send($gateway, array|string $to, string $finalContent): Model
    {
        $log = SMSlog::create([
            'sms_type' => $this->request['smsType'],
            'user_id' => $this->user->id,
            'to' => is_string($to) ? $to : join(', ', $to),
            'batch_id' => $this->batchId,
            'initiated_time' => $this->initiatedTime,
            'message' => $finalContent,
            'status' => $this->request['schedule'] ?? 1,
            'schedule_status' => $this->request['schedule'] ?? 1,
            'android_device_id' => $gateway->id
        ]);

        dispatch(new ProcessMobileSMS($gateway, $log))->delay($this->initiatedTime);
        $this->initiatedTime->addMinutes(2);
        return $log;
    }

    public static function sendMobileNotification($device): void
    {
        try {
            $arr = [
                'to' => $device->token,
                'data' => [
                    'message' => 'New Messages are available to pull.'
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

            $result = json_decode(curl_exec($curl), true);

            if (Arr::get($result, 'success', 0) != 1) {
                logger()->error(json_encode(($result)));
            }

        } catch (\Throwable $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
        }
    }

    private function combineDuplicates($n = 1): array
    {
        $i = 0;

        $messagesBuckets = [];

        foreach ($this->request['to'] as $to) {
            $message = $this->updateMessage($to, $this->messages[$i % $this->nMessages]);

            $key = md5($message);
            $messagesBuckets[$i % $n][$key]['to'] ??= [];
            $messagesBuckets[$i % $n][$key]['to'][] = $to;
            $messagesBuckets[$i % $n][$key]['message'] = $message;
            $i++;
        }

        $messages = [];

        foreach ($messagesBuckets as $message) {
            foreach ($message as $msg) {
                $messages[] = $msg;
            }
        }

        return $messages;
    }
}
