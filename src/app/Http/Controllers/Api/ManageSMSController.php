<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SMSPullRequest;
use App\Http\Requests\SMSSendRequest;
use App\Jobs\ProcessMobileSMS;
use App\Jobs\ProcessSms;
use App\Models\AndroidApiSimInfo;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;
use App\Models\SMSlog;
use App\Models\UserFcmToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vonage\SMS\Message\SMS;

class ManageSMSController extends Controller
{

    public function simInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'android_gateway_id' => 'required|exists:android_apis,id',
            'sim_number' => 'required',
            'time_interval' => 'required|integer',
            'sms_remaining' => 'required|integer',
            'send_sms' => 'required|integer',
            'status' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => $validator->errors(),
            ], 200);
        }
        $data = [
            'android_gateway_id' => $request->android_gateway_id,
            'sim_number' => $request->sim_number,
            'time_interval' => $request->time_interval,
            'sms_remaining' => $request->sms_remaining,
            'send_sms' => $request->send_sms,
            'status' => $request->status
        ];
        $simInfo = null;
        $general = GeneralSetting::first();
        if ($general->country_code != $request->country_code) {
            return response()->json([
                'status' => false,
                'data' => [
                    "message" => 'Invalid Country Code',
                ],
            ], 200);
        }
        $simInfo = AndroidApiSimInfo::where('android_gateway_id', $request->android_gateway_id)
            ->where('sim_number', $request->sim_number)->first();
        if ($simInfo) {
            $simInfo->update($data);
        } else {
            $simInfo = AndroidApiSimInfo::create($data);
        }
        return response()->json([
            'status' => true,
            'android_gateway_sim_id' => $simInfo->id,
        ], 200);
    }

    public function smsfind(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'android_gateway_sim_id' => 'required|exists:android_api_sim_infos,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => $validator->errors(),
            ], 400);
        }
        $smslogs = SMSlog::whereNull('api_gateway_id')->where('android_gateway_sim_id', $request->android_gateway_sim_id)
            ->where('status', 1)->select('id', 'android_gateway_sim_id', 'to', 'initiated_time', 'message')->take(1)->get();
        return response()->json([
            'status' => true,
            'smsLogs' => $smslogs,
        ], 200);
    }


    public function smsStatusUpdate(Request $request)
    {
        logger()->debug('Called SMS Status Update API for: ' . auth()->user()?->name);
        logger()->debug(json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:s_m_slogs,id',
            'status' => 'required|in:4,3,5',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => $validator->errors(),
            ], 400);
        }
        $smslog = SMSlog::where('id', $request->id)->first();
        if (!$smslog) {
            return response()->json([
                'status' => false,
                'message' => "No SMS found with the given ID",
            ], 200);
        }
        if ($smslog) {
            if ($request->status == 4) {
                $smslog->status = 4;
                $smslog->save();
            } else if ($request->status == 5) {
                $smslog->status = 5;
                $smslog->save();
            } else {

                $smslog->response_gateway = $request->response_gateway;
                $smslog->status = 3;
                $smslog->save();
            }
        }
        return response()->json([
            'status' => true,
            'message' => "Status updated successfully!",
        ], 200);

    }

    public function pullMessages(SMSPullRequest $request)
    {
        logger()->debug('Called SMS Pull API for: ' . auth()->user()?->name);
        logger()->debug(json_encode(request()->all()));

        $device = UserFcmToken::query()
            ->where('token', $request->device_token)
            ->orWhere('device_id', $request->device_id)
            ->firstOrFail();

        $smsLogs = SMSlog::toProcessAndroid($device->id)->limit($request->get('limit', 100))
            ->select(['id', 'android_device_id', 'message', 'to', 'batch_id', 'initiated_time', 'sms_type', 'created_at'])
            ->get();

        if($smsLogs->isNotEmpty())
        {
            SMSlog::whereIn('id', $smsLogs->pluck('id')->toArray())->update([
                'status' => SMSlog::PROCESSING
            ]);
        }

        return response()->json([
            'messages' => $smsLogs
        ]);
    }

    public function simClosed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => $validator->errors(),
            ], 400);
        }
        $array = explode(",", $request->id);
        $simInfos = AndroidApiSimInfo::whereIn('id', $array)->get();
        if ($simInfos->isNotEmpty()) {
            foreach ($simInfos as $simInfo) {
                $simInfo->status = $request->status;
                $simInfo->save();
            }
        }
        return response()->json([
            'status' => true,
        ], 200);
    }

    public function SendSMS(SMSSendRequest $request)
    {
        $user = auth()->user();

        $mobile_device = null;

        $gateway = SmsGateway::where(['user_id' => $user->id, 'user_type' => 'user', 'status' => 1])->where('default_use', 1)->first();

        if (!$gateway) {
            $mobile_device = UserFcmToken::where('user_id', $user->id)->where('device_id', $request->device_id)->first();
        }


        if (!$gateway && !$mobile_device) {

            $mobile_device = UserFcmToken::where('user_id', $user->id)->first();

            if (!$mobile_device) {
                return response()->json(['status' => 'error', 'message' => "Invalid Default gateway!. Please configure from web interface."], 400);
            }
        }

        $contactNewArray = $request->recipients;

        $general = GeneralSetting::admin();

        if ($request->schedule == 2) {
            $setTimeInDelay = $request->shedule_date;
        } else {
            $setTimeInDelay = Carbon::now();
        }

        if ($mobile_device) {
            $log = new SMSlog();
            $log->sms_type = $request->smsType == "plain" ? 1 : 2;
            $log->user_id = $user->id;
            $log->to = implode(', ', array_values($contactNewArray));
            $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
            $finalContent = $request->message;

            //adding free watermark
            if (!$user->ableToSendWithoutWatermark() && $general->free_watermark) {
                $finalContent .= "\n\n" . $general->free_watermark;
            }

            $log->message = $finalContent;
            $log->status = $request->schedule == 2 ? 2 : 1;
            $log->schedule_status = $request->schedule;
            $log->save();

            if ($log->status == 1) {
                ProcessMobileSMS::dispatch($mobile_device, $contactNewArray, $request->smsType, $finalContent, $log->id);
            } else {
                ProcessMobileSMS::dispatch($mobile_device, $contactNewArray, $request->smsType, $finalContent, $log->id)->delay(Carbon::parse($setTimeInDelay));
            }

        } else if ($gateway) {
            foreach ($contactNewArray as $key => $value) {
                $log = new SMSlog();
                if ($general->sms_gateway == 1) {
                    $log->api_gateway_id = $gateway->id;
                }
                $log->sms_type = $request->smsType == "plain" ? 1 : 2;
                $log->user_id = $user->id;
                $log->to = $value;
                $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;

                if ($request->numberGroupName && array_key_exists($value, $request->numberGroupName)) {
                    $finalContent = str_replace('{{name}}', $request->numberGroupName[$value], offensiveMsgBlock($request->message));
                } else {
                    $finalContent = str_replace('{{name}}', $value, offensiveMsgBlock($request->message));
                }

                //adding free watermark
                if (!$user->ableToSendWithoutWatermark() && $general->free_watermark) {
                    $finalContent .= "\n\n" . $general->free_watermark;
                }

                $log->message = $finalContent;
                $log->status = $request->schedule == 2 ? 2 : 1;
                $log->schedule_status = $request->schedule;
                $log->save();

                if ($general->sms_gateway == 1) {
                    if ($log->status == 1) {
                        if (count($contactNewArray) == 1 && $request->schedule == 1) {
                            ProcessSms::dispatch($value, $request->smsType, $finalContent, $gateway->credential, $gateway->gateway_code, $log->id);
                        } else {
                            ProcessSms::dispatch($value, $request->smsType, $finalContent, $gateway->credential, $gateway->gateway_code, $log->id)->delay(Carbon::parse($setTimeInDelay));
                        }
                    }
                }
            }
        }

        return response()->json([
            'status' => "success",
            'message' => "New SMS request sent, please see in the SMS history for final status",
        ]);
    }

    public function init()
    {
        $general = GeneralSetting::select('site_name')->first();
        return $general;
    }
}
