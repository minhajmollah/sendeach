<?php

namespace App\Providers;

use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Models\PaymentLog;
use App\Models\SMSlog;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WhatsappLog;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ini_set('memory_limit' , '-1');
        set_time_limit(0);
        try {

            $general = GeneralSetting::first();

            $view['general'] = $general;

            if ($general->timezone) {
                config(['app.timezone' => $general->timezone]);
                date_default_timezone_set($general->timezone);
            }

            Paginator::useBootstrap();
            $view['languages'] = Language::all();
            $view['users'] = User::orderBy('id' , 'DESC')->take(7)->get();

            if (!Session::has('lang')) {
                $default_language = Language::where('is_default' , 1)->first();
                if ($default_language) {
                    session()->put('lang' , $default_language->code);
                    session()->put('flag' , $default_language->flag);
                }
            }
            view()->share($view);
            view()->composer('admin.partials.sidebar' , function ($view) {
                $view->with([
                    'pending_sms_count' => SMSlog::where('status' , 1)->count() ,
                    'pending_whatsapp_count' => WhatsappLog::where('status' , 1)->count() ,
                    'pending_business_whatsapp_count' => WhatsappLog::where('status' , 1)
                        ->where('gateway' , WhatsappLog::GATEWAY_BUSINESS)
                        ->count() ,
                    'pending_email_count' => EmailLog::where('status' , 1)->count() ,
                    'running_support_ticket_count' => SupportTicket::whereNotIn('status' , [4])->count() ,
                    'pending_manual_payment_count' => PaymentLog::where('status' , 1)->count() ,
                ]);
            });
        } catch (\Exception $ex) {

        }
    }
}