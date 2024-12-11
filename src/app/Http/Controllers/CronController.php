<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEmail;
use App\Jobs\ProcessSms;
use App\Jobs\ProcessWhatsappMessage;
use App\Mail\NotifyLowBalanceToUser;
use App\Mail\WhatsappDesktopHealthFailure;
use App\Mail\WhatsappHealthFailureMail;
use App\Models\Admin;
use App\Models\AndroidApi;
use App\Models\AndroidApiSimInfo;
use App\Models\CreditLog;
use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;
use App\Models\SMSlog;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserWindowsToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappService\WebApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class CronController extends Controller
{
    const WHATSAPP_HEALTH_CHECK_GAP = 8;
    const DESKTOP_WHATSAPP_HEALTH_CHECK_GAP = 2;

    public function run()
    {
        $setting = GeneralSetting::admin();

//        $this->getewayCheck();
//        $this->androidApiSim();
        $this->emailSchedule();


        if (!$setting->schedule_at || Carbon::parse($setting->schedule_at)->addMinute(30) < Carbon::now()) {
            $setting->schedule_at = Carbon::now();
            $setting->save();

            $this->subscription();
            $this->debitMonthlyCharges();
            $this->smsSchedule();
            $this->notifyUserOnLowBalance();

            $this->whatsappHealthCheck();
        }

        $setting->cron_job_run = Carbon::now();
        $setting->save();

    }

    protected function androidApiSim()
    {
        $androidApis = AndroidApi::where('status', 1)->pluck('id')->toArray();
        $simInfos = AndroidApiSimInfo::whereIn('android_gateway_id', $androidApis)->where('status', 1)->pluck('id')->toArray();
        if ($simInfos) {
            $smslogs = SMSlog::whereNull('api_gateway_id')->whereNull('android_gateway_sim_id')->where('status', 1)->get();
            foreach ($smslogs as $key => $smslog) {
                $smslog->android_gateway_sim_id = $simInfos[array_rand($simInfos, 1)];
                $smslog->save();
            }
        }
    }


    protected function getewayCheck()
    {
        $smslogs = SMSlog::whereNotNull('android_gateway_sim_id')->where('status', 1)->get();
        foreach ($smslogs as $key => $smslog) {
            if ($smslog->androidGateway->status == 2) {
                $smslog->android_gateway_sim_id = null;
                $smslog->save();
            }
        }
    }


    protected function subscription()
    {
        $subscriptions = Subscription::where('status', 1)->get();
        foreach ($subscriptions as $subscription) {
            $expiredTime = $subscription->expired_date;
            $now = Carbon::now()->toDateTimeString();
            if ($now > $expiredTime) {
                $subscription->status = 2;
                $subscription->save();
            }
        }
    }

    protected function smsSchedule()
    {
        $smslogs = SMSlog::where('status', 2)->where('schedule_status', 2)->get();
        foreach ($smslogs as $smslog) {
            $expiredTime = $smslog->initiated_time;
            $now = Carbon::now()->toDateTimeString();
            if ($now > $expiredTime) {
                $general = GeneralSetting::first();
                if ($general->sms_gateway == 1) {
                    $smsGateway = SmsGateway::where('id', $general->sms_gateway_id)->first();
                    $smslog->api_gateway_id = $smsGateway->id;
                    $smslog->android_gateway_sim_id = null;
                    ProcessSms::dispatch($smslog->to, $smslog->user->phone, $smslog->message, $smsGateway->credential, $smsGateway->gateway_code, $smslog->id);
                } else {
                    $smslog->status = 1;
                    $smslog->api_gateway_id = null;
                    $smslog->android_gateway_sim_id = null;
                }
                $smslog->save();
            }
        }

        $pendingsmslogs = SMSlog::where('status', 1)->get();
        foreach ($pendingsmslogs as $pendingsms) {
            $general = GeneralSetting::first();
            if ($general->sms_gateway == 1) {
                $smsGateway = SmsGateway::where('id', $general->sms_gateway_id)->first();
                $pendingsms->api_gateway_id = $smsGateway->id;
                $pendingsms->android_gateway_sim_id = null;
                ProcessSms::dispatch($pendingsms->to, $pendingsms->user->phone, $pendingsms->message, $smsGateway->credential, $smsGateway->gateway_code, $pendingsms->id);
            } else {
                $pendingsms->status = 1;
                $pendingsms->api_gateway_id = null;
            }
            $pendingsms->save();
        }
    }

    protected function emailSchedule()
    {
        $emailLogs = EmailLog::where('status', 2)->where('schedule_status', 2)->get();
        foreach ($emailLogs as $emailLog) {
            $expiredTime = $emailLog->initiated_time;
            $now = Carbon::now()->toDateTimeString();
            if ($now > $expiredTime) {
                $general = GeneralSetting::first();
                $emailLog->schedule_status = 1;
                $emailLog->save();
                ProcessEmail::dispatch($emailLog->id, $emailLog->user_id, $emailLog->user_id ? 'user' : '');
            }
        }
    }

    private function debitMonthlyCharges()
    {
        $users = User::all();

        // 5 dollars equivalent credits will be charged
        $chargeableCredits = CreditLog::getCreditsPerDollar() * 5;

        foreach ($users as $user) {
            if ((!$user->last_charged || ($user->last_charged < now()->subDays(30))) && $user->credit > 0) {

                $user->last_charged = now();

                $user->save();

                $credit = $user->credit - $chargeableCredits < 0 ? $user->credit : $chargeableCredits;

                $creditInfo = new CreditLog();
                $creditInfo->user_id = $user->id;
                $creditInfo->credit_type = "-";
                $creditInfo->credit = $credit;
                $creditInfo->trx_number = trxNumber();
                $creditInfo->post_credit = $user->credit;
                $creditInfo->details = 'Monthly Charges for Watermark free messages.';
                $creditInfo->save();

                $user->credit -= $chargeableCredits;

                if ($user->credit < 0) {
                    $user->credit = 0;
                    if ($user->default_whatsapp_gateway === WhatsappLog::GATEWAY_BUSINESS) {
                        $user->default_whatsapp_gateway = WhatsappLog::GATEWAY_WEB;
                    }

                    $user->auto_delete_whatsapp_pc_messages = null;
                }

                $user->last_charged = now();

                $user->save();
            }
        }
    }

    public function notifyUserOnLowBalance()
    {
        $users = User::all();

        $whatsappGateway = WhatsappDevice::where('user_type', 'admin')->where('status', 'connected')->first();
        $defaultWhatsappGateway = GeneralSetting::admin()?->default_whatsapp_gateway;

        foreach ($users as $user) {

            if ($user->getAvailableDepositInDollars() > 0 && $user->getAvailableDepositInDollars() <= 3 && ($user->last_notified < now()->subDays(30) || !$user->last_notified)) {

                $user->last_notified = now();
                $user->save();

                $deposit = $user->getAvailableDepositInDollars();

                $message = "Hi {$user->name}, \n\n Your account balance is currently low, with only  \$$deposit remaining, which equates to {$user->credit} Credits.";
                $message .= "\n To ensure uninterrupted messaging without watermarks, kindly consider purchasing additional credits. Thank you.";

                // Send Email
                Mail::to($user->email)
                    ->send(new NotifyLowBalanceToUser($user));

                if ($defaultWhatsappGateway == WhatsappLog::GATEWAY_BUSINESS) {
                    $phone = WhatsappTemplate::get_user_low_balance_alert()->whatsappBusinessAccount->whatsapp_phone_numbers->first()->whatsapp_phone_number_id ?? WhatsappPhoneNumber::get_admin_phone()->whatsapp_phone_number_id;

                    ProcessWhatsappMessage::dispatch($user->phone, $phone, null,
                        WhatsappTemplate::get_user_low_balance_alert()->whatsapp_template_id, [$user->getAvailableDepositInDollars(), $user->credit], [$user->name], null);
                } else {
                    sendWebMessage([$user->phone], $whatsappGateway, null, $message);
                }

                $user->last_notified = now();
                $user->save();
            }

            $user->save();
        }
    }

    public function whatsappHealthCheck()
    {
        $devices = WhatsappDevice::with('user')->where('status', WhatsappDevice::STATUS_CONNECTED)->get();

        $admin = Admin::query()->where('username', 'admin')->first();
        $adminEmail = $admin?->email ?? "jappads@gmail.com";


        /** @var WhatsappDevice $device */
        foreach ($devices as $device) {

            if (!$device->last_health_checked_at || $device->last_health_checked_at < now()->subHours(static::WHATSAPP_HEALTH_CHECK_GAP)) {

                $device->last_health_checked_at = now();

                for ($i = 0; $i < 2; $i++) {

                    try {
                        $messageId = WebApiService::send($device,
                            $device->number . '@c.us', "SendEach Web Gateway Online Connection Status Check - Success!");

                        if (!$messageId) {
                            $device->status = WhatsappDevice::STATUS_DISCONNECTED;
                            $device->save();

                            if ($device->user) {
                                Mail::to($device->user->email)->queue(new WhatsappHealthFailureMail($device->user, $device));
                            } else {
                                Mail::to($adminEmail)->queue(new WhatsappHealthFailureMail($admin, $device));
                            }
                        } else {
                            $device->status = WhatsappDevice::STATUS_CONNECTED;
                            $device->save();
                            WebApiService::deleteMessage($device, $messageId, $device->number . '@c.us', false);
                            break;
                        }
                    } catch (Exception $exception) {
                        logger()->debug($exception->getMessage());
                        logger()->debug($exception->getTraceAsString());
                    }
                }

                $device->save();
            }
        }

        $devices = UserWindowsToken::query()->where('status', '=', WhatsappDevice::STATUS_CONNECTED)->get();

        foreach ($devices as $device) {
            if (!$device->last_called_at || $device->last_called_at < now()->subHours(static::DESKTOP_WHATSAPP_HEALTH_CHECK_GAP)) {
                $device->status = WhatsappDevice::STATUS_DISCONNECTED;
                $device->save();

                if ($device->user) {
                    Mail::to($device->user->email)->queue(new WhatsappDesktopHealthFailure($device->user, $device));
                } else {
                    Mail::to($adminEmail)->queue(new WhatsappDesktopHealthFailure($admin, $device));
                }
            }
            $device->save();
        }
    }
}
