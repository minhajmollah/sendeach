<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Image;
use App\Models\PricingPlan;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $title = "General Setting";
        $general = GeneralSetting::where('user_id',auth()->guard('admin')->user()->id)->where('user_type', 'admin')->first();
        $timeLocations = timezone_identifiers_list();
        $countries = json_decode(file_get_contents(resource_path('views/partials/country_file.json')));
        $plans = PricingPlan::select('id', 'name')->latest()->get();
        return view('admin.setting.index', compact('title', 'general', 'timeLocations','countries', 'plans'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'site_name' => 'required|max:255',
            'free_watermark' => 'nullable|string|max:255',
            'country_code' => 'required|max:30',
            'currency_name' => 'required|max:10',
            'currency_symbol' => 'required|max:10',
            'site_logo' => 'nullable|image|mimes:jpg,png,jpeg',
            'site_favicon' => 'nullable|image|mimes:jpg,png,jpeg',
//            'whatsapp_word_count' => 'required|integer|gt:0',
            'whatsapp_gateway' => ['required', Rule::in(WhatsappLog::ADMIN_GATEWAYS)],
//            'sms_word_text_count' => 'required|integer|gt:0',
//            'sms_word_unicode_count' => 'required|integer|gt:0',
            'desktop_app_version' => 'required|numeric'
        ]);

        $general = GeneralSetting::query()->where('user_id',auth()->guard('admin')->user()->id)->where('user_type', 'admin')->first();
        $general->timezone = $request->timelocation;

        $general->plan_id = $request->plan_id;
        $general->sign_up_bonus = $request->sign_up_bonus;
        $general->site_name = $request->site_name;
        $general->free_watermark = $request->free_watermark;
        $general->country_code = $request->country_code;
        $general->sms_gateway = $request->sms_gateway;
        $general->default_whatsapp_gateway = $request->whatsapp_gateway;
        $general->registration_status  = $request->registration_status;
        $general->currency_name = $request->currency_name;
        $general->currency_symbol = $request->currency_symbol;
        $general->whatsapp_word_count  = $request->whatsapp_word_count;
        $general->sms_word_text_count = $request->sms_word_text_count;
        $general->sms_word_unicode_count = $request->sms_word_unicode_count;
        $general->desktop_app_version = $request->desktop_app_version;
        $general->debug_mode = $request->debug_mode=="" ? "false" : $request->debug_mode;
        $general->maintenance_mode = $request->maintenance_mode=="" ? "false" : $request->maintenance_mode;

        $general->data ??= [];
        $general->data['whatsapp']['terms_and_conditions'] = $request->get('whatsapp-terms-and-conditions');
        $general->data['email']['terms_and_conditions'] = $request->get('email-terms-and-conditions');
        $general->data['sms']['terms_and_conditions'] = $request->get('sms-terms-and-conditions');

        if ($request->maintenance_mode_message!='') {
            $general->maintenance_mode_message = $request->maintenance_mode_message;
        }
        $general->save();
        if ($request->debug_mode==true) {
            $path = base_path('.env');
            $env_content = file_get_contents($path);
            if (file_exists($path)) {
               file_put_contents($path, str_replace('APP_ENV=production', 'APP_ENV=local', $env_content));
               $env_content = file_get_contents($path);
               file_put_contents($path, str_replace('APP_DEBUG=false', 'APP_DEBUG=true', $env_content));
            }
        }else{
            $path = base_path('.env');
            $env_content = file_get_contents($path);
            if (file_exists($path)) {
               file_put_contents($path, str_replace('APP_ENV=local', 'APP_ENV=production', $env_content));
               $env_content = file_get_contents($path);
               file_put_contents($path, str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $env_content));
            }
        }
        if($request->hasFile('site_logo')) {
            try{
                $path = filePath()['site_logo']['path'];
                if (!file_exists($path)) {mkdir($path, 0755, true);}
                Image::make($request->site_logo)->save($path . '/site_logo.png');
            }catch (\Exception $exp) {
                $notify[] = ['error', 'Logo could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }

        if($request->hasFile('desktop_app')) {
            try{
                $path = 'assets/desktop-app';

                if (!file_exists($path)) {
                    mkdir($path , 0777 , true);
                }

                move_uploaded_file($request->file('desktop_app')->getRealPath(), $path.'/'.'SendEach.zip');

            }catch (\Exception $exp) {
                $notify[] = ['error', 'Desktop APP could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }

        if($request->hasFile('site_favicon')) {
            try{
                $path = filePath()['site_logo']['path'];
                if (!file_exists($path)) {mkdir($path, 0755, true);}
                $size = explode('x', filePath()['favicon']['size']);
                Image::make($request->site_favicon)->resize($size[0], $size[1])->save($path . '/site_favicon.png');
            }catch (\Exception $exp) {
                $notify[] = ['error', 'Favicon could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }
        $notify[] = ['success', 'General Setting has been updated'];
        return back()->withNotify($notify);
    }

    public function cacheClear()
    {
        Artisan::call('optimize:clear');
        $notify[] = ['success','Cache cleared successfully'];
        return back()->withNotify($notify);
    }

    public function installPassportKey()
    {
        shell_exec('php ../artisan passport:install');
        shell_exec('php ../artisan passport:keys');
        $notify[] = ['success','Passport api key generated successfully'];
        return back()->withNotify($notify);
    }

    public function systemInfo()
    {
        $title = "System Information";
        $systemInfo['laravelversion'] = app()->version();
        $systemInfo['serverdetail'] = $_SERVER;
        $systemInfo['phpversion'] = phpversion();
        return view('admin.system_info',compact('title','systemInfo'));
    }

    public function socialLogin()
    {
        $title = "Social Login Credentials";
        return view('admin.setting.socal_login', compact('title'));
    }

    public function socialLoginUpdate(Request $request)
    {
        $this->validate($request, [
            'g_client_id' => 'required',
            'g_client_secret' => 'required',
        ]);
        $general = GeneralSetting::first();
        $google = [
            'g_client_id' => $request->g_client_id,
            'g_client_secret' => $request->g_client_secret,
        ];
        $general->s_login_google_info = $google;
        $general->save();
        $notify[] = ['success', 'Social login setting has been updated'];
        return back()->withNotify($notify);
    }

    public function frontendSection()
    {
        $title = "Manage Frontend Section";
        return view('admin.setting.frontend_section', compact('title'));
    }


    public function frontendSectionStore(Request $request)
    {
         $this->validate($request, [
            'heading' => 'required',
            'sub_heading' => 'required',
        ]);
        $general = GeneralSetting::first();
        $frontend = [
            'heading' => $request->heading,
            'sub_heading' => $request->sub_heading,
        ];
        $general->frontend_section = $frontend;
        $general->save();
        $notify[] = ['success', 'Frontend section has been updated'];
        return back()->withNotify($notify);
    }
}
