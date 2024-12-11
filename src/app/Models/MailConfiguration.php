<?php

namespace App\Models;

use App\Http\Controllers\CommonConfigurationController;
use App\Http\Utility\SendMail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder user()
 * @method static Builder admin()
 */
class MailConfiguration extends Model
{
    use HasFactory;

    protected $table = "mails";


    protected $casts = [
        'driver_information' => 'object' ,
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_IN_ACTIVE = 2;

    const TYPE_GMAIL = 'Gmail';
    const TYPE_OUTLOOK = 'Outlook';
    const TYPE_SPARKPOST = 'SparkPost';
    const TYPE_BREVO = 'Brevo';
    const TYPE_SMTP = 'SMTP';
    const TYPE_SENDGRID_API = 'SendGrid Api';
    const TYPE_SendEach = 'SendEach';

    const TYPE_SMTPS = [ self::TYPE_SMTP , self::TYPE_GMAIL , self::TYPE_OUTLOOK];

    const TYPES = [self::TYPE_GMAIL , self::TYPE_OUTLOOK , self::TYPE_SMTP , self::TYPE_SPARKPOST , self::TYPE_SENDGRID_API , self::TYPE_BREVO];

    protected $fillable = ['name' , 'status' , 'driver_information' , 'user_id' , 'user_type' , 'default_use'];

    public static function createSparkPost($userId , $userType = User::USER_TYPE_USER): Model|Builder|MailConfiguration
    {
        return MailConfiguration::query()->create([
            'name' => self::TYPE_SPARKPOST ,
            'status' => self::STATUS_IN_ACTIVE ,
            'user_id' => $userId ,
            'user_type' => $userType ,
            'driver_information' => [
                'auth_token' => '##' ,
                'from' => array('address' => '##' , 'name' => '##') ,
            ]
        ]);
    }

    public static function createBrevo($userId , $userType = 'user'): Model|Builder|MailConfiguration
    {
        return MailConfiguration::query()->create([
            'name' => self::TYPE_BREVO ,
            'status' => self::STATUS_IN_ACTIVE ,
            'user_id' => $userId ,
            'user_type' => $userType ,
            'driver_information' => [
                'api_key' => '##' ,
                'from' => array('address' => '##' , 'name' => '##') ,
            ]
        ]);
    }

    public static function createSMTP($name , $userId , $host = 'smtp.mailtrap.io' , $port = '2525' ,
                                      $encryption = 'TLS' , $userType = 'user' , $userName = '' , $password = '',$useDefault=0): Model|Builder
    {

        return MailConfiguration::query()->create([
            'name' => $name ,
            'status' => self::STATUS_IN_ACTIVE ,
            'user_id' => $userId ,
            'user_type' => $userType ,
            'default_use'=>$useDefault,
            'driver_information' => [
                'driver' => "SMTP" ,
                'host' => $host ,
                'smtp_port' => $port ,
                'from' => array('address' => '' , 'name' => '') ,
                'encryption' => $encryption ,
                'username' => $userName ,
                'password' => $password ,
                'user_id' => $userId ,
                'user_type' => 'user'
            ]
        ]);
    }
    public static function scopeUser($query , $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return $query->where([
            'user_type' => 'user' ,
            'user_id' => $userId
        ]);
    }

    public static function scopeAdmin($query , $userId = null)
    {
//        $userId = $userId ?? auth('admin')->id();

        return $query->where([
            'user_type' => User::USER_TYPE_ADMIN ,
            'user_id' => $userId
        ]);
    }

    public static function checkAndInitializeGateways($userId = null , $userType = User::USER_TYPE_USER)
    {
        $userId = $userId ?: auth()->id();

        $user = $userType == User::USER_TYPE_ADMIN ? MailConfiguration::admin($userId)->get() : MailConfiguration::user($userId)->get();
         $default_user=MailConfiguration::where('user_type','default')->get();
        if ($user->where('name' , MailConfiguration::TYPE_SENDGRID_API)->isEmpty()) {

            $mail2 = MailConfiguration::create();

            $mail2->name = MailConfiguration::TYPE_SENDGRID_API;
            $mail2->status = 2;
            $mail2->user_id = $userId;
            $mail2->user_type = $userType;
            $mail2->driver_information = [
                'app_key' => '##' ,
                'from' => array('address' => '##' , 'name' => '##') ,
            ];
            $mail2->save();
        }

        if ($user->where('name' , MailConfiguration::TYPE_SPARKPOST)->isEmpty()) {
            MailConfiguration::createSparkPost($userId , $userType);
        }

        if ($user->where('name' , MailConfiguration::TYPE_BREVO)->isEmpty()) {
            MailConfiguration::createBrevo($userId , $userType);
        }

        if ($user->where('name' , MailConfiguration::TYPE_SMTP)->isEmpty()) {
            MailConfiguration::createSMTP(MailConfiguration::TYPE_SMTP , $userId , userType: $userType);
        }

        if ($user->where('name' , MailConfiguration::TYPE_GMAIL)->isEmpty()) {
            MailConfiguration::createSMTP(MailConfiguration::TYPE_GMAIL , $userId , userType: $userType);
        }

        if ($user->where('name' , MailConfiguration::TYPE_OUTLOOK)->isEmpty()) {
            MailConfiguration::createSMTP(MailConfiguration::TYPE_OUTLOOK , $userId , userType: $userType);
        }
        if ($default_user->where('name' , MailConfiguration::TYPE_SendEach)->isEmpty()) {
            MailConfiguration::createSMTP(MailConfiguration::TYPE_SendEach , $userId , userType:'default',useDefault:1);
        }
    }

    public function updateMailConfig($data)
    {

        $this->driver_information = match ($this->name) {
            MailConfiguration::TYPE_SMTP => [
                'driver' => $data['driver'] ,
                'host' => $data['host'] ,
                'smtp_port' => $data['smtp_port'] ,
                'from' => array('address' => $data['from_address'] , 'name' => $data['from_name']) ,
                'encryption' => $data['encryption'] ,
                'username' => $data['username'] ,
                'password' => $data['password'] ,
                'user_id' => $data['user_id'] ,
                'user_type' => 'user'
            ] ,
            MailConfiguration::TYPE_SendEach => [
                'driver' => $data['driver'] ,
                'host' => $data['host'] ,
                'smtp_port' => $data['smtp_port'] ,
                'from' => array('address' => $data['from_address'] , 'name' => $data['from_name']) ,
                'encryption' => $data['encryption'] ,
                'username' => $data['username'] ,
                'password' => $data['password'] ,
                'user_id' => $data['user_id'] ,
                'user_type' => 'default',


            ] ,
            MailConfiguration::TYPE_SENDGRID_API => [
                'app_key' => $data['app_key'] ,
                'from' => array('address' => $data['from_address'] , 'name' => $data['from_name']) ,
            ] ,
            MailConfiguration::TYPE_BREVO => [
                'api_key' => $data['api_key'] ,
                'from' => array('address' => $data['from_address'] , 'name' => $data['from_name']) ,
            ] ,
            MailConfiguration::TYPE_SPARKPOST => [
                'auth_token' => $data['auth_token'] ,
                'from' => array('address' => $data['from_address'] , 'name' => $data['from_name']) ,
            ] ,
            MailConfiguration::TYPE_GMAIL => [
                'driver' => 'SMTP' ,
                'host' => 'smtp.gmail.com' ,
                'smtp_port' => 465 ,
                'from' => array('address' => $data['username'] , 'name' => $data['from_name']) ,
                'encryption' => 'SSL' ,
                'username' => $data['username'] ,
                'password' => $data['password'] ,
                'user_id' => $data['user_id'] ,
                'user_type' => $data['user_type']
            ] ,
            MailConfiguration::TYPE_OUTLOOK => [
                'driver' => 'SMTP' ,
                'host' => 'smtp-mail.outlook.com' ,
                'smtp_port' => 587 ,
                'from' => array('address' => $data['from_address'] , 'name' => $data['from_name']) ,
                'encryption' => 'TLS' ,
                'username' => $data['username'] ,
                'password' => $data['password'] ,
                'user_id' => $data['user_id'] ,
                'user_type' => $data['user_type']
            ]
        };

        $this->status = 1;
        $this->user_id = $data['user_id'];
        $this->user_type = $data['user_type'];
        $this->save();
    }

    public function test($data)
    {

        CommonConfigurationController::SetMailConfiguration(auth()->id(), $this->name);

        $general = GeneralSetting::first();

        $response = "";

        $emailTemplate = EmailTemplates::where('slug' , 'TEST_MAIL')->first();

        $messages = str_replace("{{name}}" , @$general->site_name , $emailTemplate->body);
        $messages = str_replace("{{time}}" , @Carbon::now() , $messages);

        if ($this->name === "PHP MAIL") {
            $response = SendMail::SendPHPmail($general->mail_from , $general->site_name , $data['email'] , $emailTemplate->subject , $messages);
        } elseif (in_array($this->name , MailConfiguration::TYPE_SMTPS)) {
            $response = SendMail::SendSMTPMail($this->driver_information->from->address , $data['email'] , $general->site_name , $emailTemplate->subject , $messages);
        }


        elseif ($this->name === "SendGrid Api") {
            $response = SendMail::SendGrid($this->driver_information->from->address , $general->site_name , $data['email'] , $emailTemplate->subject , $messages , @$this->driver_information->app_key);
        } elseif ($this->name == MailConfiguration::TYPE_SPARKPOST) {
            $response = SendMail::sendSparkpost($this->driver_information->from->address , $general->site_name , $data['email'] , $emailTemplate->subject , $messages , @$this->driver_information->auth_token);
        } elseif ($this->name == MailConfiguration::TYPE_BREVO) {
            $response = SendMail::sendBrevoMail($this->driver_information->from->address , $general->site_name , $data['email'] , $emailTemplate->subject , $messages , @$this->driver_information->api_key);
        }

        return $response;
    }

    public function sendMail($subject, $message, $email)
    {
        CommonConfigurationController::SetMailConfiguration(auth()->id(), $this->name);
        $general = GeneralSetting::first();

        $response = "";

        if ($this->name === "PHP MAIL") {
            $response = SendMail::SendPHPmail($general->mail_from , $general->site_name , $email , $subject , $message);
        } elseif (in_array($this->name , MailConfiguration::TYPE_SMTPS)) {

            $response = SendMail::SendSMTPMail($this->driver_information->from->address , $email , $general->site_name , $subject , $message);
        } elseif ($this->name === "SendGrid Api") {
            $response = SendMail::SendGrid($this->driver_information->from->address , $general->site_name , $email , $subject , $message , @$this->driver_information->app_key);
        } elseif ($this->name == MailConfiguration::TYPE_SPARKPOST) {
            $response = SendMail::sendSparkpost($this->driver_information->from->address , $general->site_name , $email , $subject , $message , @$this->driver_information->auth_token);
        } elseif ($this->name == MailConfiguration::TYPE_BREVO) {
            $response = SendMail::sendBrevoMail($this->driver_information->from->address , $general->site_name , $email , $subject , $message , @$this->driver_information->api_key);
        }

        return $response;
    }
}
