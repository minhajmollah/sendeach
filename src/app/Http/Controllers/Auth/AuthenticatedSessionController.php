<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $title = "User Login";
        $countries = Country::active()->select('phone_code')->distinct()->orderBy('phone_code')->get();
        return view('user.auth.login', compact('title', 'countries'));
    }

    public function send_otp(Request $request)
    {
        $request->validate([
            'phone_code' => ['required', 'string', 'exists:countries,phone_code'],
            'phone' => ['required', 'numeric', 'digits:10'],
        ]);
        $phone = $request->get('phone_code') . $request->get('phone');

        if (!preg_match("/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$/", $phone)) {
            throw ValidationException::withMessages(['phone' => "You have entered an invalid phone number!"]);
        }

        // Check if the provided phone matches any user
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            session()->flash('popup_modal', json_encode([
                'modal_class' => 'info',
                'title' => 'Phone number available!',
                'body' => "The phone number with which you're trying to login doesn't exist in the system.<br><em>Maybe try registering with it?</em>",
                'btn' => [
                    'url' => route('register'),
                    'text' => 'Register',
                ]
            ]));
            return redirect()->back()->withInput($request->all());
        }
        // Check if the status of the user is banned.
        if ($user->status == 2) {
            session()->flash('popup_modal', json_encode([
                'modal_class' => 'warning',
                'title' => "Account Banned",
                'body' => "Your account has been banned by the System Administrator.",
            ]));
            return redirect()->back()->withInput($request->all());
        }

        $user = generate_and_send_otp($user);

        // Generate the encoded otp token
        $token = RegisteredUserController::encode_token([
            'user' => $user->id,
            'otp' => $user->otp,
            'otp_time' => $user->otp_time,
        ]);

        $notify[] = ['success', 'OTP code sent to your provided number and your account\'s email address.'];
        return redirect()
            ->route('login.verify_otp_view', [
                'token' => $token,
                'resend_route' => 'login.verify_otp_view',
            ]);
    }

    public function verify_otp_view(Request $request)
    {
        try {
            $data = [
                'title' => "Verify OTP",
                'route' => route('login.verify_otp'),
                'resend_route' => $request->resend_route,
                'token' => $request->token,
                'decoded_token' => RegisteredUserController::decode_token($request->token),
            ];
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage() . " Please login again!"];
            return redirect()->route('login')->withNotify($notify);
        }
        return view('user.auth.verify_otp', $data);
    }

    public function resend_otp(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'redirect_route' => ['required'],
        ]);

        $decoded_token = RegisteredUserController::decode_token($request->token);

        if (!($decoded_token['user'] instanceof Model)) {
            $user = (array)$decoded_token['user'];

            send_otp($decoded_token['otp'], $user['phone'], $user['email'], null, $user['name']);

            $decoded_token['otp_time'] = now();
            // Generate the encoded otp token
            $token = RegisteredUserController::encode_token($decoded_token);
        } else {
            $user = $decoded_token['user'];
            if ($user) {
                $user = generate_and_send_otp($user);

                // Generate the encoded otp token
                $decoded_token['otp_time'] = now();
                $decoded_token['otp'] = $user->otp;
                $decoded_token['user'] = $user->id;
                $token = RegisteredUserController::encode_token($decoded_token);
            } else {
                $notify[] = ['error', "Malformed login token, Please try again!"];
                return redirect()->route('login')->withNotify($notify);
            }
        }

        return redirect()
            ->route($request->redirect_route, [
                'token' => $token,
                'resend_route' => $request->redirect_route,
            ]);
    }

    public function verify_otp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'token' => ['required'],
        ]);

        try {
            $decoded_token = RegisteredUserController::decode_token($request->token);

            if ($decoded_token['user']->otp != $request->code) {
                $notify[] = ['error', 'The entered OTP is incorrect!'];
                return redirect()->back()->withInput()->withNotify($notify);
            }

            if ($decoded_token['user']->status == 3) {
                $decoded_token['user']->status = 1;
                $decoded_token['user']->save();
            }
            Auth::login($decoded_token['user'], \request('remember'));
            $decoded_token['user']->update([
                'otp' => null,
                'otp_time' => null,
                'last_logged_in' => now()
            ]);
            session()->regenerate();
            return redirect()->route(RouteServiceProvider::HOME);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
            $notify[] = ['error', $e->getMessage() . " Please login again!"];
            return redirect()->route('login')->withNotify($notify);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        $lang = Session::get('lang');
        $flag = Session::get('flag');
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
