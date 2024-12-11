<?php

namespace App\Http\Utility;

use App\Models\SMSlog;
use App\Models\User;
use App\Models\UserFcmToken;
use GuzzleHttp\Client as InfoClient;
use Illuminate\Support\Str;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Textmagic\Services\TextmagicRestClient;
use Throwable;
use Twilio\Rest\Client;


class SendSMS
{

    public static function nexmo($to , $message , $credential , $smsId)
    {
        $log = SMSlog::find($smsId);
        try {
            $basic = new \Vonage\Client\Credentials\Basic($credential->api_key , $credential->api_secret);
            $client = new \Vonage\Client($basic);
            $response = $client->sms()->send(
                new \Vonage\SMS\Message\SMS($to , $credential->sender_id , $message)
            );
            $message = $response->current();
            if ($message->getStatus() == 0) {
                $log->status = SMSlog::SUCCESS;
                $log->save();
            } else {
                $log->status = SMSlog::FAILED;

            }
        } catch (\Exception $e) {
            $log->status = SMSlog::FAILED;
            $log->response_gateway = $e->getMessage();
            $log->save();
            $user = User::find($log->user_id);

        }
    }

    public static function twilio($to , $message , $credential , $smsId)
    {
        $log = SMSlog::find($smsId);
        try {
            $twilioNumber = $credential->from_number;
            $client = new Client($credential->account_sid , $credential->auth_token);
            $create = $client->messages->create('+' . $to , [
                'from' => $twilioNumber ,
                'body' => $message
            ]);

            $log->status = SMSlog::SUCCESS;
            $log->save();
        } catch (\Exception $e) {
            $log->status = SMSlog::FAILED;
            $log->response_gateway = $e->getMessage();
            $log->save();
            $user = User::find($log->user_id);

        }
    }

    public static function messageBird($to , $datacoding , $message , $credential , $smsId)
    {
        $log = SMSlog::find($smsId);
        try {
            $MessageBird = new \MessageBird\Client($credential->access_key);
            $Message = new \MessageBird\Objects\Message();
            $Message->originator = $credential->sender_id;
            $Message->recipients = array($to);
            $Message->datacoding = $datacoding;
            $Message->body = $message;
            $MessageBird->messages->create($Message);

            $log->status = SMSlog::SUCCESS;
            $log->save();
        } catch (\Exception $e) {
            $log->status = SMSlog::FAILED;
            $log->response_gateway = $e->getMessage();
            $log->save();
            $user = User::find($log->user_id);

        }
    }

    public static function textMagic($to , $message , $credential , $smsId)
    {
        $log = SMSlog::find($smsId);
        $client = new TextmagicRestClient($credential->text_magic_username , $credential->api_key);
        try {
            $result = $client->messages->create(
                array(
                    'text' => $message ,
                    'phones' => $to ,
                )
            );
            $log->status = SMSlog::SUCCESS;
            $log->save();
        } catch (\Exception $e) {
            $log->status = SMSlog::FAILED;
            $log->response_gateway = $e->getMessage();
            $log->save();

        }
    }

    public static function clickaTell($to , $message , $credentials , $smsId)
    {
        $log = SMSlog::find($smsId);
        try {
            $message = urlencode($message);
            $response = @file_get_contents("https://platform.clickatell.com/messages/http/send?apiKey=$credentials->clickatell_api_key&to=$to&content=$message");

            if ($response == false) {
                $log->status = SMSlog::FAILED;
                $log->response_gateway = "API Error, Check Your Settings";
                $log->save();

            } else {
                $log->status = SMSlog::SUCCESS;
                $log->save();
            }
        } catch (Throwable $e) {
        }
    }

    public static function infoBip($to , $message , $credentials , $smsId)
    {
        $BASE_URL = $credentials->infobip_base_url;
        $API_KEY = $credentials->infobip_api_key;

        $SENDER = $credentials->sender_id;
        $RECIPIENT = $to;
        $MESSAGE_TEXT = $message;

        $configuration = (new Configuration())
            ->setHost($BASE_URL)
            ->setApiKeyPrefix('Authorization' , 'App')
            ->setApiKey('Authorization' , $API_KEY);

        $client = new InfoClient();

        $sendSmsApi = new SendSMSApi($client , $configuration);
        $destination = (new SmsDestination())->setTo($RECIPIENT);
        $message = (new SmsTextualMessage())
            ->setFrom($SENDER)
            ->setText($MESSAGE_TEXT)
            ->setDestinations([$destination]);

        $request = (new SmsAdvancedTextualRequest())->setMessages([$message]);
        $log = SMSlog::find($smsId);
        try {
            $smsResponse = $sendSmsApi->sendSmsMessage($request);
            $log->status = SMSlog::SUCCESS;
            $log->save();
        } catch (Throwable $apiException) {
        }
    }

    public static function smsBroadcast($to , $message , $credentials , $smsId)
    {
        $log = SMSlog::find($smsId);
        try {
            $message = urlencode($message);
            $result = @file_get_contents("https://api.smsbroadcast.com.au/api-adv.php?username=$credentials->sms_broadcast_username&password=$credentials->sms_broadcast_password&to=$to&from=$credential->sender_id,&message=$message&ref=112233&maxsplit=5&delay=15");

            if ($result == Str::contains($result , 'ERROR:') || $result == Str::contains($result , 'BAD:')) {
                $log->status = SMSlog::FAILED;
                $log->response_gateway = $result;
                $log->save();


            } else {
                $log->status = SMSlog::SUCCESS;
                $log->save();
            }
        } catch (Throwable $e) {
        }
    }

    public static function mobileDevice(UserFcmToken $device , array $to , $message , $smsId)
    {
        $log = SMSlog::find($smsId);

        try {
            $arr = [
                'to' => $device->token ,
                'data' => [
                    'id' => $log->id ,
                    'recipients' => $to ,
                    'message' => $message
                ]
            ];

            $curl = curl_init();
            curl_setopt_array($curl , array(
                CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send' ,
                CURLOPT_RETURNTRANSFER => true ,
                CURLOPT_ENCODING => '' ,
                CURLOPT_MAXREDIRS => 10 ,
                CURLOPT_TIMEOUT => 0 ,
                CURLOPT_FOLLOWLOCATION => true ,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1 ,
                CURLOPT_CUSTOMREQUEST => 'POST' ,
                CURLOPT_POSTFIELDS => json_encode($arr) ,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json' ,
                    'Authorization: key=AAAABNs4OMs:APA91bHuNuon4-_avC87wr9i52a-YCzEu5hTAjHpAVfHVImsFf35a76txNfplEYv89_AezstftI-uwRFKdnAUwXPglCZh2UOFc5x2UHpcCQadc4xru1ni0w1eb1J0r3oYZDV4I_arzJX'
                ) ,
            ));

            $result = json_decode(curl_exec($curl));


            if ($result->success != 1) {
                $log->status = SMSlog::FAILED;
                $log->response_gateway = json_encode($result);
                $log->save();

            } else {
                $log->status = SMSlog::PENDING;
                $log->response_gateway = json_encode($result);
                $log->save();

            }
        } catch (\Throwable $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
        }
    }
}
