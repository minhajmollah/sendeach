<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\GeneralSetting;
use App\Models\PricingPlan;
use Carbon\Carbon;
use App\Http\Utility\SendMail;
use App\Jobs\ProcessWhatsapp;
use App\Models\PasswordReset;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use Illuminate\Validation\Rule;
use Validator;

class RegisteredUserAPIController extends Controller
{
    /**
     * Decode the login token into OTP and Find User
     *
     * @param string $token
     * @return array
     */
    private function decode_register_token(string $token)
    {
        if (!$token) {
            throw new \Exception("Verify token missing from request.", 1);
        }
        $decoded_token = json_decode(base64_decode($token), true);
        if (!isset($decoded_token['id']) || !isset($decoded_token['package']) || !isset($decoded_token['otp'])) {
            throw new \Exception("Malformed verify token.", 1);
        }

        $user = User::where('id', $decoded_token['id'])->where('status', 3)->first();
        if (!$user) {
            throw new \Exception("Malformed verify token.", 1);
        }
        return [
            'package' => $decoded_token['package'],
            'otp' => $decoded_token['otp'],
            'otp_time' => Carbon::parse($decoded_token['otp_time']),
            'user' => $user,
        ];
    }

    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $title = "Registration";
        $packages = PricingPlan::all();
        return view('user.auth.register', compact('title', 'packages'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:4'],
            'token' => ['required'],
        ]);
        try {
            $redirect_route = RouteServiceProvider::HOME;

            $decoded_token = $this->decode_register_token($request->token);
            if ($decoded_token['otp'] != $request->code) {
                $notify[] = ['error', 'The entered OTP is incorrect!'];
                return redirect()->back()->withNotify($notify);
            }
            $user = $decoded_token['user'];
            $plan = PricingPlan::find($decoded_token['package']);
            if ($plan->amount > 0) {
                $redirect_route = 'user.plan.create';
            } else {
                $user->credit = $plan->credit;
                $user->email_credit = $plan->email_credit;
                $user->whatsapp_credit = $plan->whatsapp_credit;
            }

            if ($user->status == 3) {
                $user->status = 1;
            }
            $user->save();

            $notify[] = ['success', "Your account has been activated!"];
            Auth::login($decoded_token['user']);

            return redirect()->route($redirect_route)->withNotify($notify);
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage() . " Please login again!"];
            return redirect()->route('login')->withNotify($notify);
        }
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:255'],
            'package' => ['required', Rule::exists('pricing_plans', 'id')],
        ]);


    	if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => $validator->errors(),
            ],200);
        }

        $existing_user = User::where('phone', $request->phone)->first();
        if (false) {
            session()->flash('popup_modal', json_encode([
                'modal_class' => 'info',
                'title' => 'Phone already exists!',
                'body' => "The phone number with which you're trying to register already exists in the system.<br><em>Maybe try signing in?</em>",
                'btn' => [
                    'url' => route('login'),
                    'text' => 'Sign In',
                ]
            ]));

            return response()->json([
                'status' => false,
                'data' => [
                    'title' => 'Phone already exists!',
                    'body' => "The phone number with which you're trying to register already exists in the system.<br><em>Maybe try signing in?</em>",
                 ]
            ],200);
        }

        if ($existing_user && $existing_user->status == 2) {
            return response()->json([
                'status' => false,
                'data' => [
                    'title' => "Account Banned",
                    'body' => "Your account has been banned by the System Administrator.",
                ]
            ],200);
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'status' => 3,
        ]);

        $user = generate_and_send_otp($user);

        // Generate the encoded otp token
        $token = base64_encode(json_encode([
            'id' => $user->id,
            'package' => $request->package,
            'otp' => $user->otp,
            'otp_time' => now(),
        ]));

        return response()->json([
            'status' => true,
            'success' => 'OTP code sent to your provided number.',
            'data' => [
               'token' => $token
            ]
        ],200);
    }

    public function verifyCode(Request $request)
    {
        $data = [
            'title' => "User Registration Verification",
            'route' => route('api.register'),
            'resend_route' => $request->resend_route,
            'token' => $request->token,
            'decoded_token' => $this->decode_register_token($request->token),
        ];

        return view('user.auth.verify_otp', $data);
    }
}
