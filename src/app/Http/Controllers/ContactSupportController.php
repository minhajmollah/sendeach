<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactSupportController extends Controller
{
    public function send(Request $request)
    {
        Validator::validate($request->all(), [
            'g-recaptcha-response' => 'required|captcha'
        ]);

        $emailTo = Admin::query()->first()?->email ?? "jappads@gmail.com";

        Mail::to($emailTo)->send(new ContactMail($request->message, $request->subject, $request->name, $request->email));

        return back()->with('success', 'Thanks for contacting us. We will get back to you soon.');
    }
}
