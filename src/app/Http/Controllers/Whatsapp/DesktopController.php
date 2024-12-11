<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\UserWindowsToken;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DesktopController extends Controller
{
    public function all()
    {
        $title = "WhatsApp Desktop Devices";

        $whatsAppDevices = UserWindowsToken::query()->latest()->paginate(paginateNumber());

        $generalSettings = GeneralSetting::admin();
        $gatewayStatus[WhatsappLog::GATEWAY_WEB] = Arr::get($generalSettings->data, 'whatsapp.gateway.web.status');
        $gatewayStatus[WhatsappLog::GATEWAY_DESKTOP] = Arr::get($generalSettings->data, 'whatsapp.gateway.pc.status');

        return view('whatsapp.desktop-devices', compact('title', 'whatsAppDevices', 'gatewayStatus'));
    }

    public function deleteGateway()
    {
        /** @var UserWindowsToken $device */
        $device = UserWindowsToken::findOrFail(request('id'));

        $device->deleteOrFail();

        return back()->withNotify([['success', 'Successfully Deleted the Windows device. No longer able to use this device.']]);
    }

    public function updateGateway()
    {
        /** @var UserWindowsToken $device */
        $device = UserWindowsToken::findOrFail(request('id'));
        $device->user_type = request('type');
        $device->saveOrFail();

        return back()->withNotify([['success', 'Successfully Updated the Windows device']]);
    }
}
