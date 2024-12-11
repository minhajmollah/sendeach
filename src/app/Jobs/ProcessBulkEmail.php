<?php

namespace App\Jobs;

use App\Models\EmailContact;
use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected null|int $user_id;
    private MailConfiguration $gateway;
    private array $contacts;
    private string $message;
    private null|array $emailGroupName;
    private null|array $request;
    private null|array $emailContactID;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $contacts, $gateway, $emailGroupName, $emailGroupID, $message, $request)
    {
        $this->user_id = $user_id;
        $this->contacts = $contacts;
        $this->gateway = $gateway;
        $this->emailGroupName = $emailGroupName;
        $this->message = $message;
        $this->request = $request;
        $this->emailContactID = $emailGroupID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $user = $this->user_id ? User::find($this->user_id) : null;


        $content = buildDomDocument(offensiveMsgBlock($this->message));

        if (isset($this->request['schedule']) && $this->request['schedule'] == 2) {
            $initiatedTime = Carbon::parse($this->request['schedule_date']);
            $scheduleStatus = 2;
        } else {
            $initiatedTime = Carbon::now();
            $scheduleStatus = 1;
        }

        $general = GeneralSetting::first();

        $watermark = '';
        //adding free watermark
        if ($user && !$user->ableToSendWithoutWatermark() && $general->free_watermark) {
            $watermark = "<br><br><em>" . $general->free_watermark . "</em>";
        }

        $batchId = uniqid();

        foreach ($this->contacts as $key => $value) {
            $emailLog = new EmailLog();
            $emailLog->user_id = $user?->id;
            $emailLog->from_name = $this->request['from_name'] ?? $this->gateway->driver_information->from->name;
            $emailLog->reply_to_email = $this->request['reply_to_email'] ?? $this->gateway->driver_information->from->address;
            $emailLog->sender_id = $this->gateway->id;
            $emailLog->to = $value;
            $emailLog->initiated_time = $initiatedTime;
            $emailLog->subject = $this->request['subject'] ?? '';
            $emailLog->batch_id = $batchId;

            if (array_key_exists($value , $this->emailGroupName)) {
                $emailLog->message = str_replace('{{name}}' , $this->emailGroupName ? $this->emailGroupName[$value] : $value , $content);
            } else {
                $emailLog->message = str_replace('{{name}}', $value, $content);
            }

            $emailLog->message .= $watermark;

            $emailLog->message = isset($this->emailContactID[$value]) ?
                EmailContact::replaceWithUnsubscribeLink($emailLog->message, $this->emailContactID[$value]) : $emailLog->message;

            $emailLog->status = $scheduleStatus;
            $emailLog->schedule_status = $scheduleStatus;
            $emailLog->save();

            ProcessEmail::dispatch($emailLog->id, $user?->id,
                $user ? User::USER_TYPE_USER : User::USER_TYPE_ADMIN)->delay($initiatedTime)->onQueue('emails');
        }
    }
}