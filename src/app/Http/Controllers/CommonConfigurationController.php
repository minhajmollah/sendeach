<?php

namespace App\Http\Controllers;

use App\Models\MailConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class CommonConfigurationController extends Controller
{
    public static function SetMailConfiguration($user_id = null , $name = MailConfiguration::TYPE_SMTP)
    {
      if($name=='SendEach'){

        $mail = MailConfiguration::where('user_type' , 'default')->where('status' , 1)->where('name' , $name)->first();

      }
        if (User::find($user_id)) {
            $mail = MailConfiguration::where('user_type' , 'user')->where('status' , 1)->where('name' , $name)->where('user_id' , $user_id)->first();
        }
        if($name=='SMTP') {
            $mail = MailConfiguration::where('user_type' , 'admin')->where('status' , 1)->where('name' , $name)->first();
        }


        if (isset($mail) && in_array($mail->name , MailConfiguration::TYPE_SMTPS)) {
            $config = array(
                'driver' => $mail->driver_information->driver ,
                'mailers' => [
                    'smtp' => [
                        'host' => $mail->driver_information->host ?? "smtp" ,
                        'port' => $mail->driver_information->smtp_port ,
                        'encryption' => $mail->driver_information->encryption ?? "ssl" ,
                        'username' => $mail->driver_information->username ,
                        'password' => $mail->driver_information->password ,
                    ] ,
                ] ,
                'pretend' => false ,
                'from' => [
                    'address' => $mail->driver_information->from->address ,
                    'name' => $mail->driver_information->from->name
                ] ,
            );
            Config::set('mail.driver' , $config['driver']);
            Config::set('mail.mailers.smtp' , $config['mailers']['smtp']);
            Config::set('mail.from' , $config['from']);
            Config::set('mail.host' , $config['mailers']['smtp']['host']);
            Config::set('mail.port' , $config['mailers']['smtp']['port']);
            Config::set('mail.encryption' , $config['mailers']['smtp']['encryption']);
            Config::set('mail.username' , $config['mailers']['smtp']['username']);
            Config::set('mail.password' , $config['mailers']['smtp']['password']);
        }
    }
}