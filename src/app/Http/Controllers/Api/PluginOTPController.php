<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PublicEmailOTPSendRequest;
use App\Http\Requests\Api\PublicOTPSendRequest;
use App\Mail\PublicOTP;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class PluginOTPController extends Controller
{
    public function sendOTP(PublicOTPSendRequest $request): JsonResponse
    {
        $admin = GeneralSetting::admin();
        $defaultWhatsappGateway = $admin->default_whatsapp_gateway;
        $watermark = $admin->free_watermark;

        $domain = $request->domain;
        $otp = $request->otp;

        // OTP Message string
        $messages = "Your OTP code is $otp, valid for 5 minutes.

            Please enter the code to complete your registration or login on $domain

            $watermark";

        if ($defaultWhatsappGateway == WhatsappLog::GATEWAY_BUSINESS) {
            $template = WhatsappTemplate::get_otp_template();

            $log = sendOTPViaBusinessGateway(WhatsappPhoneNumber::get_admin_phone() , $request->phone , $template , $otp);
        } else if ($defaultWhatsappGateway == WhatsappLog::GATEWAY_DESKTOP) {
            $log = sendDesktopMessage([$request->phone] , null , null , $messages);
        } else {
            $log = sendWebMessage([$request->phone] , null , null , $messages);
        }

        if ($log instanceof JsonResponse) {
            return $log;
        }

        if ($log) {
            return response()->json([
                'status' => 'success' ,
                'data' => ['message' => 'OTP has been sent successfully.' ,] ,
            ]);
        }

        return response()->json([
            'status' => 'failed' ,
            'data' => ['message' => 'Unable to send OTP.' ,] ,
        ] , 400);
    }

    public function sendOTPViaEmail(PublicEmailOTPSendRequest $request): JsonResponse
    {
        try {
            $message = (new PublicOTP($request->otp , $request->domain))->render();

            MailConfiguration::query()->where('user_id', null)->where('default_use', 1)->first()
                ->sendMail("Your One-Time Password (OTP) for SendEach", $message, $request->email);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failed' ,
                'data' => ['message' => 'Unable to send OTP. Please try again later.' ,] ,
            ] , 400);
        }

        return response()->json([
            'status' => 'success' ,
            'data' => ['message' => 'OTP has been sent successfully to your Email.' ,] ,
        ]);
    }
}
