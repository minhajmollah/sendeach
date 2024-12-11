<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\CommonConfigurationController;
use App\Http\Controllers\Controller;
use App\Http\Utility\SendMail;
use App\Models\EmailTemplates;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MailConfigurationController extends Controller
{
    public function index()
    {
        $title = "Mail Configuration";

        // if user setting does not exist in the table copy the details from admin and create that for user so the user can add his own detail
        MailConfiguration::checkAndInitializeGateways();

        $mails = MailConfiguration::query()
        ->where(function($query) {
            $query->where([
                'user_type' => User::USER_TYPE_USER,
                'user_id' => auth()->id()
            ])
            ->orWhere('user_type', 'default');
        })
            ->orderBy('user_type')
        ->paginate(paginateNumber());

        return view('user.mail.index' , compact('title' , 'mails'));
    }

    public function edit($id)
    {
        $title = "Mail updated";
        /** @var MailConfiguration $mail */
        $mail = MailConfiguration::where('id' , $id)->where('user_type' , 'user')->where('user_id' , auth()->user()->id)->firstOrFail();

        return view('user.mail.edit' , compact('title' , 'mail'));
    }

    public function mailUpdate(Request $request , $id)
    {
        $data = $this->validate($request , [
            'driver' => "required_if:name,==,smtp" ,
            'host' => "required_if:name,==,smtp" ,
            'smtp_port' => "required_if:name,==,smtp" ,
            'encryption' => "required_if:name,==,smtp" ,
            'username' => "required_if:name,==,smtp" ,
            'password' => "required_if:name,==,smtp" ,
            'from_address' => "required_if:name,==,smtp" ,
            'from_name' => "required_if:name,==,smtp" ,
        ]);

        $mail = MailConfiguration::query()->where('id', $id)->firstOrFail();

        $data = $request->all();

        $data['user_id'] = auth()->id();
        $data['user_type'] = User::USER_TYPE_USER;

        $mail->updateMailConfig($data);

        $notify[] = ['success' , ucfirst($mail->name) . ' mail method has been updated'];

        return back()->withNotify($notify);
    }

    public function sendMailMethod(Request $request)
    {
        $this->validate($request , [
            'id' => 'required|exists:mails,id'
        ]);
        $defaultmailmethod = MailConfiguration::whereIn('user_type' , ['user','default'])->where('default_use',1)->update(['default_use'=>0]);


        $makeDefaultMailMethods = MailConfiguration::where('user_type' , 'default')->where('id' , $request->id)->first();
        if ($makeDefaultMailMethods) {
            $makeDefaultMailMethods->default_use = 1;
            $makeDefaultMailMethods->save();

            $notify[] = ['success' , 'Email method has been updated'];
        return back()->withNotify($notify);
        }

        $makeDefaultMailMethod = MailConfiguration::where('user_type' , 'user')->where('user_id' , auth()->user()->id)->where('id' , $request->id)->first();
        if ($makeDefaultMailMethod) {
            $makeDefaultMailMethod->default_use = 1;
            $makeDefaultMailMethod->save();
        }

        $notify[] = ['success' , 'Email method has been updated'];
        return back()->withNotify($notify);
    }


    public function globalTemplate()
    {
        $title = "Global template";
        return view('admin.mail.global_template' , compact('title'));
    }

    public function globalTemplateUpdate(Request $request)
    {
        $this->validate($request , [
            'mail_from' => 'required|email' ,
            'body' => 'required' ,
        ]);
        $general = GeneralSetting::first();
        $general->mail_from = $request->mail_from;
        $general->email_template = $request->body;
        $general->save();
        $notify[] = ['success' , 'Global email template has been updated'];
        return back()->withNotify($notify);

    }

    public function mailTester(Request $request , $id)
    {
        $this->validate($request, ['email' => 'required|email']);

        $mailConfiguration = MailConfiguration::query()->where('id' , $id)->where('user_id', auth()->id())->firstOrFail();

        $user = auth()->user();
        $user->data ??= [];
        $user->data['test_email'] = $request->email;
        $user->saveOrFail();

        $response = $mailConfiguration->test($request->all());

        if (!$response) {
            $notify[] = ['success' , "Successfully sent mail, please check your inbox or spam"];
        } else {
            $notify[] = ['error' , "Mail Configuration Error, Please check your mail configuration properly"];
        }

        return back()->withNotify($notify);
    }
}
