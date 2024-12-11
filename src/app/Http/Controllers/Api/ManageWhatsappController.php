<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\WhatsappMessagingTrait;
use App\Http\Requests\Api\MessageBusinessSendRequest;
use App\Http\Requests\Api\MessageRequest;
use App\Http\Requests\Api\MessageUpdateRequest;
use App\Http\Requests\Api\MessageWebSendRequest;
use App\Http\Requests\Api\OTPSendRequest;
use App\Http\Requests\Api\PublicOTPSendRequest;
use App\Models\GeneralSetting;
use App\Models\UserWindowsToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManageWhatsappController extends Controller
{
    use WhatsappMessagingTrait;

    public function sendBusiness(MessageBusinessSendRequest $request)
    {

        $request->handle();

        $logIds = $this->sendBusinessWhatsappMessage($request , auth()->user());

        if ($logIds instanceof JsonResponse) {
            return $logIds;
        }

        if ($logIds) {
            return response()->json([
                'status' => 'success' ,
                'data' => ['log_ids' => $logIds , 'message' => 'Message Sent Successfully' ,] ,
            ]);
        }

        return response()->json([
            'status' => 'error' ,
            'data' => ['message' => 'No Message has been Sent']
        ] , 400);
    }

    public function sendMessage(MessageWebSendRequest $request): JsonResponse
    {

        $user = auth()->user();
        $defaultGateway = $user->default_whatsapp_gateway ?? WhatsappLog::GATEWAY_WEB;

        $message = $request->message;

        if (!auth()->user()->ableToSendWithoutWatermark()) {
            if ($watermark = GeneralSetting::getFreeWatermark()) {
                $message .= "\n\n*" . $watermark . "*";
            }
        }

        if ($defaultGateway == WhatsappLog::GATEWAY_DESKTOP) {
            $log = sendDesktopMessage($request->recipients , $request->whatsapp_device , $user , $message);
        } else {
            $log = $this->sendWebMessage($request->recipients , $request->whatsapp_device , $user , $message);
        }

        if ($log instanceof JsonResponse) {
            return $log;
        }

        if ($log) {
            return response()->json([
                'status' => 'success' ,
                'data' => ['log_ids' => $log ,
                    'message' => 'New WhatsApp Message request sent, please see in the WhatsApp Log history for final status'] ,
            ]);
        }

        return response()->json([
            'status' => 'error' ,
            'data' => ['message' => 'No Message has been Sent' ,] ,
        ] , 400);
    }


    public function sendOTP(OTPSendRequest $request): JsonResponse
    {
        $user = auth()->user();
        $defaultGateway = $user->default_whatsapp_gateway ?? WhatsappLog::GATEWAY_WEB;

        $otp = randomOtp(6);

        $token = encrypt(base64_encode(json_encode([
            'phone' => $request->phone ,
            'otp_time' => now() ,
            'otp' => $otp
        ])));


        if ($defaultGateway == WhatsappLog::GATEWAY_BUSINESS_OWN) {
            $template = WhatsappTemplate::query()->where('whatsapp_template_id' , $user->whatsapp_business_otp_template_id)->first();

            if (!$template) {
                return response()->json([
                    'status' => 'failed' ,
                    'data' => ['message' => 'You have chosen default gateway as your own Business Gateway and no OTP template is chosen. Please goto whatsapp settings to choose it or change the gateway.' ,] ,
                ] , 400);
            }

            $phone = $template?->whatsappBusinessAccount->whatsapp_phone_numbers()->where('status' , 'APPROVED')->inRandomOrder()->first();

            if (!$phone) {
                return response()->json([
                    'status' => 'failed' ,
                    'data' => ['message' => 'You have chosen default gateway as your own Business Gateway and no Approved Phone number has found. Please change the gateway.' ,] ,
                ] , 400);
            }
        }

        $message = "Here is your SendEach OTP [{$otp}]. It was generated on " . now()->format("M d, Y h:i A") . ". Please don't share it with anyone.";

        $log = match ($defaultGateway) {
            WhatsappLog::GATEWAY_DESKTOP => sendDesktopMessage([$request->phone] , $request->whatsapp_device , $user , $message) ,
            WhatsappLog::GATEWAY_BUSINESS => $this->sendOTPViaBusinessGateway(WhatsappPhoneNumber::get_admin_phone() , $request->phone , WhatsappTemplate::get_otp_template() , $otp) ,
            WhatsappLog::GATEWAY_BUSINESS_OWN => $this->sendOTPViaBusinessGateway($phone , $request->phone , $template , $otp) ,
            default => $this->sendWebMessage([$request->phone] , $request->whatsapp_device , $user , $message)
        };

        if ($log instanceof JsonResponse) {
            return $log;
        }

        if ($log) {
            return response()->json([
                'status' => 'success' ,
                'data' => ['message' => 'OTP has been sent successfully.' ,] ,
                'token' => $token
            ]);
        }

        return response()->json([
            'status' => 'failed' ,
            'data' => ['message' => 'Unable to send OTP.' ,] ,
        ] , 400);
    }

    public function verifyOTP(): JsonResponse
    {
        $token = request('token');
        $otp = request('otp');


        $token = json_decode(base64_decode(decrypt($token)));


        if ($token && $token->otp && $token->otp_time && $token->phone) {

            if (!$token->otp_time > now()->subMinutes(15)) {
                return response()->json([
                    'status' => 'failed' ,
                    'data' => ['message' => 'OTP has been expired.' ,] ,
                ] , 400);
            } elseif ($token->otp != $otp) {
                return response()->json([
                    'status' => 'failed' ,
                    'data' => ['message' => 'Invalid OTP.' ,] ,
                ] , 400);
            }

            return response()->json([
                'status' => 'success' ,
                'data' => ['message' => 'OTP has been verified successfully.' ,] ,
            ]);
        }

        return response()->json([
            'status' => 'failed' ,
            'data' => ['message' => 'Invalid Login Token.' ,] ,
        ] , 400);
    }

    public function messages(MessageRequest $request): JsonResponse
    {
        $logs = WhatsappLog::query()->orderBy('id' , 'DESC')
            ->where('user_id' , auth()->id())
            ->when($request->log_ids , function ($query) use ($request) {
                return $query->whereIn('id' , [$request->log_ids]);
            })
            ->when($request->status , function ($query) use ($request) {
                return $query->where('status' , $request->status);
            })
            ->when($request->start_date && $request->end_date , function ($query) use ($request) {
                return $query->whereBetween('created_at' , [$request->start_date , $request->end_date]);
            })
            ->when(!empty($request->fields) , function ($query) use ($request) {
                return $query->select($request->fields);
            })->paginate(paginateNumber());

        return response()->json($logs);
    }

    public function getTemplates(Request $request): JsonResponse
    {
        $templates = WhatsappTemplate::query();

        $templates = $templates
            ->where('status' , 'APPROVED')
            ->where(function ($query) {
                return $query->where('user_id' , auth()->id())
                    ->Orwhere('is_public' , true);
            })
            ->select('created_at' ,
                'updated_at' , 'whatsapp_template_id' ,
                'whatsapp_business_id' , 'name' ,
                'category' , 'language' ,
                'rejected_reason' , 'components')
            ->get();

        return response()->json([
            'status' => 'success' ,
            'data' => ['templates' => $templates]
        ]);
    }

    public function getPhones(Request $request): JsonResponse
    {
        $whatsappPhoneNumbers = WhatsappPhoneNumber::query();

        $whatsappPhoneNumbers = $whatsappPhoneNumbers
            ->where(function ($query) {
                return $query->where('user_id' , auth()->id())
                    ->Orwhere('is_public' , true);
            })
            ->select('created_at' ,
                'updated_at' , 'display_phone_number' ,
                'whatsapp_phone_number_id' , 'verified_name' ,
                'code_verification_status' , 'quality_rating' ,
                'whatsapp_business_id')
            ->get();

        return response()->json([
            'status' => 'success' ,
            'data' => ['phones' => $whatsappPhoneNumbers]
        ]);
    }
}
