<?php

namespace App\Http\Utility;

use App\Http\Controllers\CommonConfigurationController;
use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SendEmail
{

    public static function SendPHPmail($emailFrom , $sitename , $emailTo , $subject , $messages , $emailLog , $user_id)
    {
        CommonConfigurationController::SetMailConfiguration($user_id);

        $headers = "From: $sitename <$emailFrom> \r\n";
        $headers .= "Reply-To: $sitename <$emailFrom> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        try {
            @mail($emailTo , $subject , $messages , $headers);
            $emailLog->status = EmailLog::SUCCESS;
        } catch (\Exception $e) {
            $emailLog->status = EmailLog::FAILED;
            $emailLog->response_gateway = $e->getMessage();
        }
        $emailLog->save();
    }

    public static function SendSmtpMail($emailFrom , $fromName , $emailTo , $replyTo , $subject , $messages , $emailLog , $user_id , $name = 'SMTP')
    {
        try {
            CommonConfigurationController::SetMailConfiguration($user_id , $name);

            Mail::send([] , [] , function ($message) use ($messages , $emailFrom , $fromName , $emailTo , $replyTo , $subject) {
                $message->to($emailTo)
                    ->replyTo($replyTo)
                    ->subject($subject)
                    ->from($emailFrom , $fromName)
                    ->html($messages , 'text/html' , 'utf-8');
            });
            $emailLog->status = EmailLog::SUCCESS;
            $emailLog->save();
        } catch (\Exception $e) {
            self::updateErrorLog($emailLog , $e);
        }
    }

    public static function SendGrid($emailFrom , $fromName , $emailTo , $subject , $messages , $emailLog , $credentials)
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($emailFrom , $fromName);
        $email->addTo($emailTo);
        $email->setSubject($subject);
        $email->addContent("text/html" , $messages);
        $sendgrid = new \SendGrid(@$credentials);

        try {
            $response = $sendgrid->send($email);
            if ($response->statusCode() != 200) {
                $emailLog->status = EmailLog::FAILED;
                $emailLog->response_gateway = json_encode($response->json());
                $emailLog->save();
                $user = User::find($emailLog->user_id);
                if ($user != '') {
                    $user->email_credit += 1;
                    $user->save();
                }
            } else {
                $emailLog->status = EmailLog::SUCCESS;
                $emailLog->save();
            }
        } catch (\Exception $e) {
            self::updateErrorLog($emailLog , $e);
        }
    }

    public static function sendSparkpost($emailFrom , $fromName , $emailTo , $subject , $messages , $emailLog , $token , $toName = '' , $replyTo = null)
    {
        try {
            $response = Http::withToken($token)
                ->post('https://api.sparkpost.com/api/v1/transmissions' , [
                    "options" => ["sandbox" => true] ,
                    "content" => [
                        "from" => $emailFrom ,
                        "subject" => $subject ,
                        "html" => $messages
                    ] ,
                    "recipients" => [
                        ["address" => $emailTo]
                    ]
                ]);

            self::updateEmailLog($response , $emailLog);

        } catch (\Exception $e) {
            self::updateErrorLog($emailLog , $e);
        }
    }

    public static function sendBrevoMail($emailFrom , $fromName , $emailTo , $subject , $messages , $emailLog , $apiKey , $toName = '' , $replyTo = null)
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey
            ])
                ->acceptJson()
                ->asJson()
                ->post('https://api.brevo.com/v3/smtp/email' , [
                    "sender" => [
                        "email" => $emailFrom ,
                        "name" => $fromName ,
                    ] ,
                    "to" => [
                        ["email" => $emailTo , 'name' => $toName ?: $emailTo]
                    ] ,
                    'replyTo' => ['email' => $replyTo ?: $emailFrom] ,
                    "subject" => $subject ,
                    "htmlContent" => $messages
                ]);

            self::updateEmailLog($response , $emailLog);

        } catch (\Exception $e) {
            self::updateErrorLog($emailLog , $e);
        }

        return null;
    }

    private static function updateEmailLog(\GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response $response , $emailLog): void
    {
        if ($response->failed()) {
            $emailLog->status = EmailLog::FAILED;
            $emailLog->response_gateway = json_encode($response->json());
            $emailLog->save();
            $user = User::find($emailLog->user_id);
            if ($user != '') {
                $user->email_credit += 1;
                $user->save();
            }
        } else {
            $emailLog->status = EmailLog::SUCCESS;
            $emailLog->save();
        }
    }

    /**
     * @param $emailLog
     * @param \Exception $e
     * @return void
     */
    private static function updateErrorLog($emailLog , \Exception $e): void
    {
        $emailLog->status = EmailLog::FAILED;
        $emailLog->response_gateway = $e->getMessage();
        $emailLog->save();
        $user = User::find($emailLog->user_id);
        if ($user != '') {
            $user->email_credit += 1;
            $user->save();
        }
    }

}