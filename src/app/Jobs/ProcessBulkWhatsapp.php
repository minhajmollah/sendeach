<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WhatsappLog;

use Illuminate\Database\Eloquent\Model;


class ProcessBulkWhatsapp extends ProcessBulkMobileMessages
{

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user = User::query()->findOrFail($this->user);
        $this->setWatermark();
        $this->initiatedTime = $this->request['schedule_date']->addMinutes(2);

        $whatsappGatewayIds = $this->gateways;
        $i = 0;
        $n = count($whatsappGatewayIds);

        $addSecond = 1;

        $isAntiBlockEnabled = \Illuminate\Support\Arr::get($this->user->data, 'whatsapp.anti_block');

        foreach ($this->request['to'] as $to) {

            $message = $this->messages[$i % $this->nMessages];

            $addSecond += $whatsappGatewayIds[$i % $n]['delay_time'] + 1;
            $this->initiatedTime->addSeconds($addSecond);

            $finalContent = $this->updateMessage($to, $message);

            if ($isAntiBlockEnabled) {
                // Anti Block Strategy to delay the initiated time.
                $cacheKey = "user:: {$this->user->id}::web-gateway::{$whatsappGatewayIds[$i % $n]['id']}";
                WhatsappLog::delayInitiatedTime($cacheKey, $this->initiatedTime);
            }

            $i++;

            $this->send($whatsappGatewayIds[$i % $n]['id'], $to, $finalContent);
        }
    }


    public function send($gateway, string $to, string $finalContent): WhatsappLog|Model
    {
        $log = WhatsappLog::query()->create([
            'whatsapp_id' => $gateway,
            'user_id' => $this->user->id,
            'to' => $to,
            'message' => $finalContent,
            'gateway' => WhatsappLog::GATEWAY_WEB,
            'initiated_time' => $this->initiatedTime,
            'status' => $this->request['schedule'] ?? 1,
            'schedule_status' => $this->request['schedule'] ?? 1,
            'document' => $this->request['document'] ?? null,
            'audio' => $this->request['audio'] ?? null,
            'image' => $this->request['image'] ?? null,
            'video' => $this->request['video'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
            'batch_id' => $this->batchId
        ]);

        ProcessWhatsapp::dispatch($finalContent, $to, $log->id)->delay($this->initiatedTime)->onQueue('whatsapp');

        return $log;
    }

}
