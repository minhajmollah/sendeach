<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsGateway;
use App\Models\GeneralSetting;
use App\Models\UserFcmToken;
use Illuminate\Support\Arr;

class SmsGatewayController extends Controller
{

    public function index()
    {
        $title = "SMS API Gateway list";
        $smsGateways = SmsGateway::where('user_type', 'user')->where('user_id', auth()->user()->id)->orderBy('id', 'asc')->get();
        $devices = UserFcmToken::where(['user_id' => auth()->user()->id])->orderBy('id', 'DESC')->paginate(paginateNumber());

        // if user setting does not exist in the table copy the details from admin and create that for user so the user can add his own detail
        if ($smsGateways->count() == 0) {
            $adminSMSGateway = SmsGateway::where('user_type', 'admin')->get();
            foreach ($adminSMSGateway as $gateway) {
                $userSMSGateway = SmsGateway::create();
                $userSMSGateway->gateway_code = $gateway->gateway_code;
                $userSMSGateway->name = $gateway->name;
                if ($gateway->name == 'nexmo') {
                    $userSMSGateway->credential = [
                        "api_key" => "#",
                        "api_secret" => "#",
                        "sender_id" => "#"
                    ];
                } elseif ($gateway->name == 'twilio') {
                    $userSMSGateway->credential = [
                        "account_sid" => "#",
                        "auth_token" => "#",
                        "from_number" => "",
                        "sender_id" => ""
                    ];
                } elseif ($gateway->name == 'message Bird') {
                    $userSMSGateway->credential = [
                        "access_key" => "#",
                        "sender_id" => "#"
                    ];
                } elseif ($gateway->name == 'Text Magic') {
                    $userSMSGateway->credential = [
                        "api_key" => "#",
                        "text_magic_username" => "#",
                        "sender_id" => "#"
                    ];
                } elseif ($gateway->name == 'Clickatell') {
                    $userSMSGateway->credential = [
                        "clickatell_api_key" => "#",
                        "sender_id" => "#"
                    ];
                } elseif ($gateway->name == 'InfoBip') {
                    $userSMSGateway->credential = [
                        "infobip_base_url" => "#",
                        "infobip_api_key" => "#",
                        "sender_id" => "#"
                    ];
                } elseif ($gateway->name == 'SMS Broadcast') {
                    $userSMSGateway->credential = [
                        "sms_broadcast_username" => "#",
                        "sms_broadcast_password" => "#",
                        "sender_id" => "#"
                    ];
                }

                $userSMSGateway->status = 2;
                $userSMSGateway->user_id = auth()->user()->id;
                $userSMSGateway->user_type = 'user';
                $userSMSGateway->default_use = $gateway->default_use;

                $userSMSGateway->save();
            }

        }
        $smsGateways = SmsGateway::where([
            'user_type' => 'user',
            'user_id' => auth()->user()->id
        ])->orderBy('id', 'asc')->paginate(paginateNumber());
        return view('user.sms_gateway.index', compact('title', 'smsGateways', 'devices'));
    }

    public function edit($id)
    {
        $title = "SMS API Gateway update";
        $smsGateway = SmsGateway::findOrFail($id);
        return view('user.sms_gateway.edit', compact('title', 'smsGateway'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:1,2',
        ]);
        $smsGateway = SmsGateway::findOrFail($id);
        $parameter = [];
        foreach ($smsGateway->credential as $key => $value) {
            $parameter[$key] = $request->sms_method[$key];
        }
        $smsGateway->credential = $parameter;
        $smsGateway->status = 1;
        $smsGateway->save();
        $notify[] = ['success', 'SMS Gateway has been updated'];
        return back()->withNotify($notify);
    }


    public function defaultGateway(Request $request)
    {
        //find the already set default gateway to make it not default
        $defaultSmsGateway = SmsGateway::where('user_id', auth()->id())->where('user_type', 'user')->where('default_use', 1)->first();
        if ($defaultSmsGateway) {
            $defaultSmsGateway->default_use = 0;
            $defaultSmsGateway->status = 2;
            $defaultSmsGateway->save();
        }

        if ($request->sms_gateway) {
            $smsGateway = SmsGateway::findOrFail($request->sms_gateway);
            $smsGateway->status = 2;
            $smsGateway->default_use = 1;
            $smsGateway->save();
        }

        $notify[] = ['success', 'Default SMS Gateway has been updated'];
        return back()->withNotify($notify);
    }

    public function updateAntiBlock()
    {
        $user = auth()->user();

        $user->data = $user->data ?: [];

        $isEnabled = (bool)\request('is_enabled');
        Arr::set($user->data, 'sms.anti_block', $isEnabled);
        $user->saveOrFail();

        return back()->withNotify([['success', $isEnabled ? 'Successfully enabled the Anti Block System.' : 'Disabled the Anti Block System.']]);
    }

    public function delete()
    {
        $this->validate(\request(), ['id' => 'required']);
        if (UserFcmToken::query()->where('user_id', auth()->id())->where('id', request('id'))->delete()) {
            return back()->withNotify([['success', 'Successfully Removed the Android Device from SendEach. Please make sure you logout your account on SendEach mobile APP.']]);
        }

        return back()->withNotify([['error', 'Unable to remove android device. Please logout manually from your mobile device.']]);
    }
}
