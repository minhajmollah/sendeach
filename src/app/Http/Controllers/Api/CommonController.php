<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Jobs\ProcessWhatsapp;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CommonController extends Controller
{
    /**
     * Decode the login token into OTP and Find User
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'fcm_token' => ['required'],
            'device_id' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $validator->errors()
                ]
            ],200);
        }

        $rec = UserFcmToken::where('device_id',$request->device_id)->first();
        if($rec){
            $rec->user_id = $request->user()->id;
            $rec->token = $request->fcm_token;
            $rec->save();
            $message = "token updated successfully";
        }else{
            $fcm = New UserFcmToken;
            $fcm->user_id = $request->user()->id;
            $fcm->device_id = $request->device_id;
            $fcm->token = $request->fcm_token;
            $fcm->save();
            $message = "token saved successfully";
        }


        return response()->json([
            'status' => true,
            'data' => [
                'message' => $message
            ]
        ],200);
    }

    public function existFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'fcm_token' => ['required'],
            'device_id' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $validator->errors()
                ]
            ],200);
        }

        $fcm = UserFcmToken::where('token',$request->fcm_token)->where('device_id',$request->device_id)->first();;
        $exist_fcm_token = false;
        if($fcm){
            $exist_fcm_token = true;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'exist_fcm_token' => $exist_fcm_token
            ]
        ],200);
    }

    public function deleteFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'device_id' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $validator->errors()
                ]
            ],200);
        }

        $fcm = UserFcmToken::where('device_id',$request->device_id)->first();;
        if(!$fcm){
            return response()->json([
                'status' => true,
                'data' => [
                    'message' => 'Device id not found!'
                ]
                ],200);
        }

        $fcm->delete();
        return response()->json([
            'status' => true,
            'data' => [
                 'message' => 'Device id deleted successfully!'
            ]
        ],200);
    }

    public function sendSMS(Request $request)
    {
        //$data = json_decode($request->all(), true);
        //echo "<pre>"; print_r($request->payload);
        $validator = Validator::make($request->all(),[
            'device_id' => ['required'],
            'recipients' => ['required', 'array'],
            'message' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $validator->errors()
                ]
            ],200);
        }

        $fcm = UserFcmToken::where('device_id',$request->device_id)->first();;
        if(!$fcm){
            return response()->json([
                'status' => true,
                'data' => [
                    'exist' => "no device id found"
                ]
                ],200);
        }

        //echo gettype($request->recipients); die;
        $arr = array('to'=>$fcm->token,'notification'=>array('title'=>'SMS Request','body'=>'Click on the notification to send the sms.'),'data'=>array('recipients'=>$request->recipients,'message'=>$request->message));
        // return response()->json($arr);

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($arr),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: key=AAAABNs4OMs:APA91bHuNuon4-_avC87wr9i52a-YCzEu5hTAjHpAVfHVImsFf35a76txNfplEYv89_AezstftI-uwRFKdnAUwXPglCZh2UOFc5x2UHpcCQadc4xru1ni0w1eb1J0r3oYZDV4I_arzJX'
          ),
        ));

        $response = curl_exec($curl);

        return response()->json([
            'status' => true,
            'data' => [
                'message' => json_decode($response)
            ]
        ],200);
    }

    public function existDeviceID(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'device_id' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $validator->errors()
                ]
            ],200);
        }

        $fcm = UserFcmToken::where('device_id',$request->device_id)->first();;
        $exist_fcm_token = false;
        if($fcm){
            $exist_fcm_token = true;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'exist' => $exist_fcm_token
            ]
        ],200);
    }
}
