<?php

namespace App\Jobs;

use App\Models\SMSlog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProcessBulkSMS extends ProcessBulkMobileMessages
{

    public function handle()
    {
        $this->user = User::query()->findOrFail($this->user);
        $this->setWatermark();
        $this->request['smsType'] = ($this->request['smsType'] ?? null) == "plain" ? 1 : 2;
        $this->initiatedTime = $this->request['schedule_date'];

        $isAntiBlockEnabled = \Illuminate\Support\Arr::get($this->user->data, 'sms.anti_block');

        $i = 0;

        foreach ($this->request['to'] as $to) {
            $message = $this->messages[$i % $this->nMessages];
            $finalContent = $this->updateMessage($to, $message);
            $i++;

            $this->send($this->gateways, $to, $finalContent);
        }
    }

    public function send($gateway, string $to, string $finalContent): Model
    {
        $log = SMSlog::create([
            'sms_type' => $this->request['smsType'] ,
            'user_id' => $this->user->id,
            'to' => $to,
            'batch_id' => $this->batchId,
            'initiated_time' => $this->initiatedTime,
            'message' => $finalContent,
            'status' => $this->request['schedule'] ?? 1,
            'api_gateway_id' => $gateway->id,
            'schedule_status' => $this->request['schedule'] ?? 1,
        ]);

        ProcessSms::dispatchNow($to, $this->request['smsType'], $finalContent, $gateway->credential, $gateway->gateway_code, $log->id);

        return $log;
    }
}
