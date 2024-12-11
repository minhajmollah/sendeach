<?php

namespace App\Jobs;

use App\Http\Utility\SendEmail;
use App\Models\Admin;
use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEmail
{
    use Dispatchable , InteractsWithQueue , Queueable , SerializesModels , Batchable;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $emailLogId;

    protected $user_id;

    protected $user_type;


    public function __construct($emailLogId , $user_id = '' , $user_type = '', $delay = null)
    {
        $this->emailLogId = $emailLogId;
        $this->user_id = $user_id;
        $this->user_type = $user_type;
        $this->delay = null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $emailLog = EmailLog::find($this->emailLogId);
        if (!$emailLog) {
            return;
        }
        $general = GeneralSetting::first();
        $emailMethod = MailConfiguration::whereId($emailLog->sender_id)->first();

        if ($emailLog->user_id) {
            $user = User::where('id' , $emailLog->user_id)->first();
            if ($emailMethod->name != "PHP MAIL") {
                $emailFrom = $emailMethod->driver_information->from->address;
                $emailFromName = $emailLog->from_name ?: $emailMethod->driver_information?->from->name;
                $emailReplyTo = $emailLog->reply_to_email ?: $user->email;
            } else {
                $emailFrom = $general->mail_from;
                $emailFromName = $general->site_name;
                $emailReplyTo = $general->mail_from;
            }

        } else {
            if ($emailMethod->name != "PHP MAIL") {
                $admin = Admin::where('id' , 1)->first();
                $emailFrom = $emailMethod->driver_information->from->address;
                $emailFromName = $emailLog->from_name == '' ? $emailMethod->driver_information->from->name : $emailLog->from_name;
                $emailReplyTo = $emailLog->reply_to_email == '' ? $admin->email : $emailLog->reply_to_email;
            } else {
                $emailFrom = $general->mail_from;
                $emailFromName = $general->site_name;
                $emailReplyTo = $general->mail_from;
            }
        }

        $emailTo = $emailLog->to;
        $subject = $emailLog->subject;
        $messages = $emailLog->message;
        if ($emailMethod->name == MailConfiguration::TYPE_SendEach) {
            $defualt_getway = MailConfiguration::where('user_type' , 'default_getway')->first();
            if( $defualt_getway->name== MailConfiguration::TYPE_BREVO){
                SendEmail::sendBrevoMail($emailFrom , $emailFromName , $emailTo , $subject , $messages , $emailLog ,
                @$emailMethod->driver_information->api_key , replyTo: $emailReplyTo);
            }
            elseif ($emailMethod->name == "SendGrid Api") {
                SendEmail::SendGrid($emailFrom , $emailFromName , $emailTo , $subject , $messages , $emailLog ,
                    @$emailMethod->driver_information->app_key);
            } elseif ($emailMethod->name == MailConfiguration::TYPE_SPARKPOST) {
                SendEmail::sendSparkpost($emailFrom , $emailFromName , $emailTo , $subject , $messages , $emailLog ,
                    @$emailMethod->driver_information->auth_token , replyTo: $emailReplyTo);
            }
            elseif (in_array($defualt_getway->name , MailConfiguration::TYPE_SMTPS)) {
                SendEmail::SendSmtpMail($emailFrom , $emailFromName , $emailTo , $emailReplyTo , $subject ,
                    $messages , $emailLog , $this->user_id , $emailMethod->name);
            }

        }
        else if ($emailMethod->name == "PHP MAIL") {
            SendEmail::SendPHPmail($emailFrom , $emailFromName , $emailTo , $subject , $messages , $emailLog ,
                $this->user_id);
        } elseif (in_array($emailMethod->name , MailConfiguration::TYPE_SMTPS)) {
            SendEmail::SendSmtpMail($emailFrom , $emailFromName , $emailTo , $emailReplyTo , $subject ,
                $messages , $emailLog , $this->user_id , $emailMethod->name);
        }
        elseif ($emailMethod->name == "SendGrid Api") {
            SendEmail::SendGrid($emailFrom , $emailFromName , $emailTo , $subject , $messages , $emailLog ,
                @$emailMethod->driver_information->app_key);
        } elseif ($emailMethod->name == MailConfiguration::TYPE_SPARKPOST) {
            SendEmail::sendSparkpost($emailFrom , $emailFromName , $emailTo , $subject , $messages , $emailLog ,
                @$emailMethod->driver_information->auth_token , replyTo: $emailReplyTo);
        } elseif ($emailMethod->name == MailConfiguration::TYPE_BREVO) {
            SendEmail::sendBrevoMail($emailFrom , $emailFromName , $emailTo , $subject , $messages , $emailLog ,
                @$emailMethod->driver_information->api_key , replyTo: $emailReplyTo);
        }

    }

    public function failed($exception)
    {
        $data = EmailLog::find($this->emailLogId);
        if ($data->status == EmailLog::PENDING) {
            $data->status = EmailLog::FAILED;
            $data->save();
        }
    }
}