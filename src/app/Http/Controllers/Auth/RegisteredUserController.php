<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsapp;
use App\Models\Country;
use App\Models\PricingPlan;
use App\Models\User;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $title = "Registration";
        $countries = Country::active()->select('phone_code')->distinct()->orderBy('phone_code')->get();
        return view('user.auth.register', compact('title', 'countries'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'token' => ['required'],
        ]);

        try {
            $redirect_route = RouteServiceProvider::HOME;

            $decoded_token = $this->decode_token($request->token);

            if ($decoded_token['otp'] != $request->code) {
                $notify[] = ['error', 'The entered OTP is incorrect!'];
                return redirect()->back()->withNotify($notify);
            }

            /** @var User $user */
            $user = User::query()->create(array_merge((array)$decoded_token['user'], [
                'status' => 1,
                'otp' => null,
                'otp_time' => null,
                'last_logged_in' => now()
            ]));

            $user->create_default_groups();
            $user->add_to_admin_groups();

            session()->regenerate(true);

            $notify[] = ['success', "Your account has been activated!"];
            Auth::login($user, (bool)\request('remember'));

            return redirect()->route($redirect_route)->withNotify($notify);
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage() . " Please login again!"];
            return redirect()->route('login')->withNotify($notify);
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_code' => ['required', 'string', 'exists:countries,phone_code'],
            'phone' => ['required', 'numeric', 'digits:10'],
        ]);
        $phone = $request->get('phone_code') . $request->get('phone');

        if (!preg_match("/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$/", $phone)) {
            throw ValidationException::withMessages(['phone' => "You have entered an invalid phone number!"]);
        }

        $existing_user = User::where('phone', $phone)->first();
        if ($existing_user && $existing_user->status != 2) {
            session()->flash('popup_modal', json_encode([
                'modal_class' => 'info',
                'title' => 'Phone already exists!',
                'body' => "The phone number with which you're trying to register already exists in the system.<br><em>Maybe try signing in?</em>",
                'btn' => [
                    'url' => route('login'),
                    'text' => 'Sign In',
                ]
            ]));
            return redirect()->back()->withInput($request->all());
        }
        if ($existing_user && $existing_user->status == 2) {
            session()->flash('popup_modal', json_encode([
                'modal_class' => 'warning',
                'title' => "Account Banned",
                'body' => "Your account has been banned by the System Administrator.",
            ]));
            return redirect()->back()->withInput($request->all());
        }

        $otp = randomOtp(6);

        send_otp($otp, $phone, $request->email, null, $request->name);

        // Generate the encoded otp token
        $token = static::encode_token([
            'user' => [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
            ],
            'otp' => $otp,
            'otp_time' => now(),
        ]);

        $notify[] = ['success', 'OTP code sent to your provided number and email.'];
        return redirect(route('registration.verify.code', [
            'token' => $token,
            'resend_route' => 'registration.verify.code',
        ]))->withNotify($notify);
    }

    public function verifyCode(Request $request)
    {
        $data = [
            'title' => "User Registration Verification",
            'route' => route('register'),
            'resend_route' => $request->resend_route,
            'token' => $request->token,
            'decoded_token' => $this->decode_token($request->token),
        ];

        return view('user.auth.verify_otp', $data);
    }

    public static function decode_token(string $token)
    {
        if (!$token) {
            throw ValidationException::withMessages(["Verify token missing from request."]);
        }
        $decoded_token = json_decode(decrypt(base64_decode($token)), true);

        if (!isset($decoded_token['user'])) {
            throw ValidationException::withMessages(["Malformed verify token."]);
        }

        $otpTime = ($decoded_token['otp_time'] ?? '');
        $otp = ($decoded_token['otp'] ?? '');
        if (is_string($decoded_token['user']) || is_int($decoded_token['user'])) {
            $user = User::findOrFail($decoded_token['user']);
        } else {
            $user = (object)$decoded_token['user'];
        }

        if (!$user || !$otp || !$otpTime || !Carbon::parse($otpTime)->addMinutes(10)->gt(now())) {
            throw ValidationException::withMessages(["Malformed verify token. OTP Expired."]);
        }

        $decoded_token['user'] = $user;
        return $decoded_token;
    }

    public static function encode_token($data)
    {
        return base64_encode(encrypt(json_encode($data)));
    }
}
