<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Jobs\ProcessWhatsapp;
use App\Models\PricingPlan;
use App\Models\User;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{
    /**
     * Decode the login token into OTP and Find User
     *
     * @param string $token
     * @return array
     */
    private function decode_login_token(string $token)
    {
        if (!$token) {
            throw new \Exception("Verify token missing from request.", 1);
        }
        $decoded_token = json_decode(base64_decode($token), true);
        if (!isset($decoded_token['phone'])) {
            throw new \Exception("Malformed verify token.", 1);
        }

        $user = User::where('phone', $decoded_token['phone'])->first();
        if (!$user || !$user->otp || !$user->otp_time || !$user->otp_time?->addMinutes(10)->gt(now())) {
            throw new \Exception("Malformed verify token.", 1);
        }
        return [
            'phone' => $decoded_token['phone'],
            'user' => $user,
            'otp_time' => $user->otp_time,
        ];
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'phone' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $validator->errors()
                ]
            ],200);
        }

        // Check if there is a whatsapp gateway available
        $whatsappGateway = WhatsappDevice::where('user_type', 'admin')->where('status', 'connected')->first();

        // Check if the provided phone matches any user
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'data' => [
                    'title' => 'Phone number available!',
                    'message' => "The phone number with which you're trying to login doesn't exist in the system. Maybe try registering with it?",
                ]
            ],200);
        }
        // Check if the status of the user is active.
        if ($user->status == 2) {
             return response()->json([
                'status' => false,
                'data' => [
                    'title' => "Account Banned",
                    'message' => "Your account has been banned by the System Administrator.",
                ]
            ],200);
        }

        $user = generate_and_send_otp($user);

        // Generate the encoded otp token
        $token = base64_encode(json_encode([
            'phone' => $user->phone,
            'otp_time' => $user->otp_time,
        ]));

        return response()->json([
            'status' => true,
            'data' => [
                'message' => 'OTP code sent to your provided number and your account\'s email address.',
                'token' => $token
            ]
        ],200);
    }

    public function resend_otp(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'redirect_route' => ['required'],
        ]);

        $decoded_token = $this->decode_login_token($request->token);

        $user = User::where('phone' , $decoded_token['phone'])->first();
        if (!$user) {
            $notify[] = ['error', "Malformed login token, Please try again!"];
            return redirect()->route('login')->withNotify($notify);
        }

        $user = generate_and_send_otp($user);

        // Generate the encoded otp token
        $token = base64_encode(json_encode([
            'phone' => $decoded_token['phone'],
            'otp_time' => $user->otp_time,
        ]));

        $notify[] = ['success', 'OTP code has been re-sent to your provided number and account\'s email address.'];
        return redirect()
            ->route($request->redirect_route, [
                'token' => $token,
                'resend_route' => $request->redirect_route,
            ]);
    }

    public function verify_otp_login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => ['required', 'string', 'size:6'],
            'token' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $validator->errors()
                ]
            ],200);
        }

        try {
            $decoded_token = $this->decode_login_token($request->token);

            if ($decoded_token['user']->otp != $request->code) {
                return response()->json([
                    'status' => false,
                    'data' => [
                        'message' => 'The entered OTP is incorrect!'
                    ]
                ],200);
            }

            if ($decoded_token['user']->status == 3) {
                $decoded_token['user']->status = 1;
                $decoded_token['user']->save();
            }

            $decoded_token['user']->update([
                'otp' => null,
                'otp_time' => null,
            ]);

            /** @var User $user */
            $user = $decoded_token['user'];
            $token =  $user->createToken('MyApp', [User::ABILITY_SEND_SMS, User::ABILITY_SEND_WHATSAPP])->plainTextToken;

            return response()->json([
                'status' => true,
                'data' => [
                    'message' => "login successfully",
                    'token' => $token,
                    'user' => $decoded_token['user'],
                ]
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage() . " Please login again!"
                ]
            ],200);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $lang =  Session::get('lang');
        $flag =  Session::get('flag');
        $request->user()->currentAccessToken()->delete();
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return $this->loggedOut(request(), $lang, $flag) ?: redirect('/');
    }

    protected function loggedOut(Request $request, $lang, $flag)
    {
        Session::put('lang', $lang);
        Session::put('flag', $flag);
    }
}
