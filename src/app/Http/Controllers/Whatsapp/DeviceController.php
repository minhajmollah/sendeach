<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBulkEmail;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\User;
use App\Models\UserWindowsToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function gatewayStatus()
    {
        $title = "Gateway Status";

        $generalSettings = GeneralSetting::admin();
        $gatewayStatus = $generalSettings->getWhatsappGatewayStatus();

        return view('whatsapp.gateway-status', compact('title', 'gatewayStatus'));
    }

    public function gatewayStatusUpdate()
    {

        $generalSettings = GeneralSetting::admin();
        $generalSettings->data = $generalSettings->data ?: [];

        $gatewayStatus = $generalSettings->getWhatsappGatewayStatus();

        Arr::set($generalSettings->data, 'whatsapp.gateway.'.WhatsappLog::GATEWAY_WEB.'.status', \request('web_gateway_status'));
        Arr::set($generalSettings->data, 'whatsapp.gateway.'.WhatsappLog::GATEWAY_DESKTOP.'.status', \request('pc_gateway_status'));


        $emailGateway = MailConfiguration::where([
            'user_type' => User::USER_TYPE_ADMIN,
            'status' => 1,
            'default_use' => 1
        ])->first();

        if ($gatewayStatus[WhatsappLog::GATEWAY_WEB] != ($status = \request('web_gateway_status'))) {
            $devices = WhatsappDevice::with('user')
                ->whereNotNull('user_id')->where('status', WhatsappDevice::STATUS_CONNECTED)
                ->groupBy('user_id')
                ->select('user_id')
                ->get();

            $this->alertUser($devices, $status, $emailGateway, WhatsappLog::GATEWAY_WEB);
        }

        if ($gatewayStatus[WhatsappLog::GATEWAY_DESKTOP] != ($gatewayPCStatus = \request('pc_gateway_status'))) {

            $devices = UserWindowsToken::with('user')
                ->whereNotNull('user_id')->where('status', WhatsappDevice::STATUS_CONNECTED)
                ->groupBy('user_id')
                ->select('user_id')
                ->get();

            $this->alertUser($devices, $gatewayPCStatus, $emailGateway, WhatsappLog::GATEWAY_DESKTOP);
        }

        $generalSettings->saveOrFail();

        return back()->withNotify([['success', 'Updated the Gateway status successfully']]);
    }

    public function updateAuthenticationDefault()
    {
        $user = auth()->user();

        $data = Validator::make(request()->all(), ['whatsapp_gateway' => ['required', Rule::in(WhatsappLog::GATEWAYS)]])->validate();

        if ($data['whatsapp_gateway'] == WhatsappLog::GATEWAY_BUSINESS && $user->credit < 1) {
            return redirect()->route('user.credits.create')->withNotify([['error', 'Please Buy some credits to switch to business gateway']]);
        }
        $generalSettings = GeneralSetting::admin();
        $gatewayStatus = $generalSettings->getWhatsappGatewayStatus();


        if(!$gatewayStatus[$data['whatsapp_gateway']])
        {
            return back()->withNotify([['error', 'The gateway is currently unavailable. Please try using another gateway that is online.']]);
        }

        $user->default_whatsapp_gateway = $data['whatsapp_gateway'];

        $user->save();

        return back()->withNotify([['success', 'Updated Your default gateway']]);
    }

    public function updateMarketingDefault()
    {
        $user = auth()->user();

        $data = Validator::make(request()->all(), ['whatsapp_gateway' => ['required', Rule::in(WhatsappLog::GATEWAYS)]])->validate();

        if ($data['whatsapp_gateway'] == WhatsappLog::GATEWAY_BUSINESS && $user->credit < 1) {
            return redirect()->route('user.credits.create')->withNotify([['error', 'Please Buy some credits to switch to business gateway']]);
        }

        $generalSettings = GeneralSetting::admin();
        $gatewayStatus = $generalSettings->getWhatsappGatewayStatus();

        if(!$gatewayStatus[$data['whatsapp_gateway']])
        {
            return back()->withNotify([['error', 'The gateway is currently unavailable. Please try using another gateway that is online.']]);
        }

        $user->default_whatsapp_gateway = $data['whatsapp_gateway'];

        $user->save();

        // TODO update Desktop route

        $sendWhatsappRoute = match (auth()->user()->default_whatsapp_gateway) {
            \App\Models\WhatsappLog::GATEWAY_BUSINESS => 'user.business.whatsapp.account.create',
            \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN => 'user.business.whatsapp.account.create',
            \App\Models\WhatsappLog::GATEWAY_DESKTOP => 'user.desktop.gateway.whatsapp.create',
            default => 'user.gateway.whatsapp.create',
        };

        return redirect()->route($sendWhatsappRoute)->withNotify([['success', 'Updated Your default gateway']]);
    }

    public function alertUser(\Illuminate\Database\Eloquent\Collection|array $devices, mixed $status, $emailGateway, string $gateway): void
    {
        if ($devices->isNotEmpty()) {

            foreach ($devices as $device) {
                if ($status == WhatsappLog::GATEWAY_STATUS_OFFLINE) {
                    $message = (new \App\Mail\AlertGatewayOfflineMail($device->user, $gateway))->render();

                    dispatch(new ProcessBulkEmail(null, [$device->user->email], $emailGateway, [],
                        null, $message, ['subject' => "WhatsApp $gateway Gateway Maintenance: Change Your Gateway Today to Avoid Disruptions"]));
                } else {
                    $message = (new \App\Mail\AlertGatewayOnlineMail($device->user, WhatsappLog::GATEWAY_WEB))->render();

                    dispatch(new ProcessBulkEmail(null, [$device->user->email], $emailGateway, [],
                        null, $message, ['subject' => "WhatsApp $gateway Gateway is Back Online"]));
                }
            }
        }
    }

    public function updateAntiBlock()
    {
        $user = auth()->user();

        $user->data = $user->data ?: [];

        $isEnabled = (bool)\request('is_enabled');
        Arr::set($user->data, 'whatsapp.anti_block', $isEnabled);
        $user->saveOrFail();

        return back()->withNotify([['success', $isEnabled ? 'Successfully enabled the Anti Block System.' : 'Disabled the Anti Block System.']]);
    }
}
