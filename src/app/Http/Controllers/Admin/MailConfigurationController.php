<?php

namespace App\Http\Controllers\Admin;

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

        MailConfiguration::checkAndInitializeGateways(null, User::USER_TYPE_ADMIN);


        $mails = MailConfiguration::query()
        ->whereIn('user_type', [User::USER_TYPE_ADMIN, 'default'])
        ->paginate(paginateNumber());
        $defualt=MailConfiguration::query()->where('user_type','default')->get();


        return view('admin.mail.index' , compact('title' , 'mails','defualt'));
    }

    public function edit($id)
    {
        $title = "Mail updated";
        $mail = MailConfiguration::where('id' , $id)->whereIn('user_type' , ['admin','default'])->first();
        return view('admin.mail.edit' , compact('title' , 'mail'));
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

        $mail = MailConfiguration::query()->findOrFail($id);

        $data = $request->all();
        $data['user_id'] = null;
        $data['user_type'] = $mail->user_type;


        $mail->updateMailConfig($data);

        $notify[] = ['success' , ucfirst($mail->name) . ' mail method has been updated'];
        return back()->withNotify($notify);
    }

    public function sendMailMethod(Request $request)
    {
        $this->validate($request , [
            'id' => 'required|exists:mails,id'
        ]);

        $selected_method = MailConfiguration::where('user_type' , 'admin')->where('id' , $request->id)->first();

        $driver_information=  $selected_method->driver_information;

        $makeDefaultMailMethods = MailConfiguration::where('user_type' , 'default')->first();
        if($request->sendeach){
            if ($makeDefaultMailMethods) {
                $makeDefaultMailMethods->driver_information =$driver_information;
                $makeDefaultMailMethods->save();
                $defualt_getway_update = MailConfiguration::where('user_type' , 'default_getway')->first();
                $common_getway=MailConfiguration::where('default_use', 2)->first();
                $default_selected_getway=MailConfiguration::where('user_type', 'admin')->where('default_use', 1)->first();
                if( $defualt_getway_update){
                    if($common_getway){
                    if($common_getway->id==$selected_method->id){
                        if($default_selected_getway){
                            $default_selected_getway->default_use=1;
                            $default_selected_getway->save();
                        }

                    }else{
                        $common_getway->default_use=0;
                        $common_getway->save();
                    }
                }
                if(!$default_selected_getway){
                    if($common_getway){
                        $system = MailConfiguration::where('name', $common_getway->name)
                          ->where('user_type', 'admin')->first();
                          $system->default_use=1;
                          $system->save();

                    }




                }

                    $defualt_getway_update->name=$selected_method->name;
                    $defualt_getway_update->driver_information=$driver_information;
                    $defualt_getway_update->user_type='default_getway';
                    $defualt_getway_update->default_use=3;
                    $defualt_getway_update->status=1;
                    $defualt_getway_update->save();
                    $selected_method->default_use=2;
                    $selected_method->save();



                }else{
                    $selectedMethod=MailConfiguration::create();
                    $selectedMethod->name=$selected_method->name;
                    $selectedMethod->driver_information=$driver_information;
                    $selectedMethod->user_type='default_getway';
                    $selectedMethod->default_use=3;
                    $selectedMethod->status=1;
                    $selectedMethod->save();

                }


            }
            $notify[] = ['success' , 'Email method SendEach has been updated'];
            return back()->withNotify($notify);
        }


        // check if already default method is set
        //set it back to not default to make the requested one default
        $defaultmailmethod = MailConfiguration::where('user_type' , 'admin')->where('default_use' , 1)->first();
        if ($defaultmailmethod) {
            $defaultmailmethod->default_use = 0;
            $defaultmailmethod->save();
        }

        // it is saving on the general table //now moved to tha configuration table to make it available for multiple user
        //each user can set his own default send method
        // $general = GeneralSetting::first();
        // $general->email_gateway_id = $mail->id;
        // $general->save();

        // check if the method exist and make it default
        $makeDefaultMailMethod = MailConfiguration::where('user_type' , 'admin')->where('id' , $request->id)->first();
        if ($makeDefaultMailMethod) {
            $makeDefaultMailMethod->default_use =  $makeDefaultMailMethod->default_use==2 ? $makeDefaultMailMethod->default_use :1;
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
        // CommonConfigurationController::SetMailConfiguration();

        $mailConfiguration = MailConfiguration::where('id' , $id)->first();


        $response = $mailConfiguration?->test($request->all());

        if ($response == null) {
            $notify[] = ['success' , "Successfully sent mail, please check your inbox or spam"];
        } else {
            $notify[] = ['error' , "Mail Configuration Error, Please check your mail configuration properly"];
        }

        return back()->withNotify($notify);
    }
}
