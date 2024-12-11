<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsGateway;
use App\Models\GeneralSetting;
use App\Models\UserFcmToken;

class SmsGatewayController extends Controller
{

    public function index()
    {
    	$title = "SMS Gateway list";
    	$smsGateways = SmsGateway::where('user_type', 'admin')->orderBy('id','asc')->paginate(paginateNumber());
        $devices = UserFcmToken::where('user_type', 'admin')->orderBy('id', 'asc')->paginate(paginateNumber());
        $all_devices = UserFcmToken::where('user_type', '!=', 'admin')->orderBy('id', 'asc')->get();

    	return view('admin.sms_gateway.index', compact('title', 'smsGateways', 'devices', 'all_devices'));
    }

    public function edit($id)
    {
    	$title = "SMS API Gateway update";
    	$smsGateway = SmsGateway::findOrFail($id);
    	return view('admin.sms_gateway.edit', compact('title', 'smsGateway'));
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
        $smsGateway->status = $request->status;
        $smsGateway->save();
        $notify[] = ['success', 'SMS Gateway has been updated'];
        return back()->withNotify($notify);
    }


    public function defaultGateway(Request $request)
    {
        //find the already set default gateway to make it not default
    	$defaultSmsGateway = SmsGateway::where('user_id', auth()->guard('admin')->user()->id)->where('user_type', 'admin')->where('default_use', 1)->first();
    	if($defaultSmsGateway) {
    	    $defaultSmsGateway->default_use = 0;
    	    $defaultSmsGateway->save();
    	}

    	$smsGateway = SmsGateway::findOrFail($request->sms_gateway);
        $smsGateway->default_use = 1;
        $smsGateway->save();
    	$notify[] = ['success', 'Default SMS Gateway has been updated'];
        return back()->withNotify($notify);
    }

    public function attach_device(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'exists:user_fcm_tokens,id'],
        ]);

        $updated = UserFcmToken::where('id', $request->device_id)->update(['user_type' => 'admin']);
        if($updated){
            $notify[] = ['success', 'Mobile Device attached to the admin account!'];
        } else {
            $notify[] = ['error', 'Error connecting mobile device!'];
        }
        return back(302, [], route('admin.gateway.sms.index'))->withNotify($notify);
    }

    public function remove_device(UserFcmToken $device)
    {
        $updated = $device->update(['user_type' => 'user']);
        if($updated){
            $notify[] = ['success', 'Mobile Device detached from the admin account!'];
        } else {
            $notify[] = ['error', 'Error removing mobile device!'];
        }

        return back(302, [], route('admin.gateway.sms.index'))->withNotify($notify);
    }
}
