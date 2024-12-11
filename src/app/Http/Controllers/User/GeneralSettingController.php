<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Artisan;
use Image;
use App\Models\PricingPlan;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $title = "General Setting";
        $general = GeneralSetting::where('user_id',auth()->user()->id)->where('user_type', 'user')->first();
        if(!$general) {
            $general = GeneralSetting::create();
            $general->user_id = auth()->user()->id;
            $general->user_type = 'user';
            $general->save();
        }
        $countries = json_decode(file_get_contents(resource_path('views/partials/country_file.json')));
        return view('user.setting.index', compact('title', 'general','countries'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'country_code' => 'required|max:30',
        ]);
        
        $general = GeneralSetting::where('user_id',auth()->user()->id)->where('user_type', 'user')->first();
        $general->country_code = $request->country_code;
        $general->sms_gateway = $request->sms_gateway;
        $general->save();
        
        $notify[] = ['success', 'General Setting has been updated'];
        return back()->withNotify($notify);
    }
}
