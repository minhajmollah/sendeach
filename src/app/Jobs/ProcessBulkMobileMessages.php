<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ProcessBulkMobileMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $watermark;
    protected Carbon $initiatedTime;
    protected string $batchId;
    protected array $messages;
    protected int $nMessages;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $user, public mixed $gateways, public array $request)
    {
        $this->batchId = uniqid();
        $this->request['schedule_date'] = $this->request['schedule_date'] ?? now();
        $this->messages = $this->request['message'];
        $this->nMessages = count($this->messages);
    }


    abstract public function handle();

    abstract protected function send($gateway, string|array $to, string $finalContent);


    protected function setWatermark(): self
    {
        $this->watermark = '';

        //adding free watermark
        if (!$this->user->ableToSendWithoutWatermark() && $free_watermark = GeneralSetting::admin()?->free_watermark) {
            $this->watermark = "\n\n*" . $free_watermark . "*";
        }

        foreach ($this->messages as $i => $message) {
            $this->messages[$i] = $message . $this->watermark;
        }

        return $this;
    }


    protected function updateMessage(mixed $to, mixed $message): string
    {
        if (isset($this->request['numberGroupName'][$to])) {
            $finalContent = str_replace('{{name}}', $this->request['numberGroupName'][$to], offensiveMsgBlock($message));
        } else {
            $finalContent = str_replace('{{name}}', $to, offensiveMsgBlock($message));
        }

        if (isset($this->request['groupIDContact'][$to])) {
            $finalContent = Contact::replaceWithUnsubscribeLink($finalContent, $this->request['groupIDContact'][$to]);
        }

        return $finalContent;
    }
}
