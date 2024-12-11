<?php

namespace App\Http\Controllers\User\Whatsapp;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageSendRequest;
use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\UserWindowsToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DesktopController extends Controller
{
    use WhatsappControllerTrait;

    public function create()
    {
        $title = "Compose Message";
        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();
        $groups = $user->group()->get();
        $templates = $user->template()->get();

        $availableCredits = (int)$user->credit;
        $availableDepositAmount = round($user->getAvailableDepositInDollars(), 2);

        $devices = UserWindowsToken::query()->where('user_id', \auth()->id())->where('status', WhatsappDevice::STATUS_CONNECTED)->get();

        if ($devices->isEmpty()) {
            $notify[] = ['error', 'Please connect at least one device to send messages!'];
            return redirect()->route('user.desktop.gateway.whatsapp.create')->withNotify($notify);
        }

        return view('user.whatsapp_desktop.create', compact('title', 'groups', 'devices',
            'templates', 'availableDepositAmount', 'availableCredits'));
    }

    public function send(MessageSendRequest $request)
    {
        $request->handle();

        $user = \auth()->user();

        $device = UserWindowsToken::connected(\auth()->id())
            ->where('id', $request->whatsapp_device)
            ->get();

        if ($device->isEmpty()) {
            $device = UserWindowsToken::connected(auth()->id())->get();
        }

        if ($device->isEmpty()) {
            return back()->withInput()
                ->withNotify([['error', 'No Desktop Device added. Please add it by installing desktop app.']]);
        }

        $general = GeneralSetting::first();

        $contacts = $request->to;

        $watermark = '';

        //adding free watermark
        if (!$user->ableToSendWithoutWatermark() && $general->free_watermark) {
            $watermark = "\n\n*" . $general->free_watermark . "*";
        }

        $request->getAttachment();

        $messageLogs = [];

        $deviceIds = $device->pluck('id')->toArray();
        $i = 0;
        $n = $device->count();

        $batchId = uniqid();

        $messages = [$request->message];

        if ($spinMessage = $request->spinMessage) {
            $messages = array_merge($messages, $spinMessage);
        }
        $initiatedTime = Carbon::parse($request->schedule_date ?? now());
        $isAntiBlockEnabled = \Illuminate\Support\Arr::get($user->data, 'whatsapp.anti_block');
        $messageCount = count($messages);

        foreach ($contacts as $to) {
            $message = $messages[$i % $messageCount];

            if (isset($request->numberGroupName[$to])) {
                $finalContent = str_replace('{{name}}', $request->numberGroupName[$to], offensiveMsgBlock($message));
            } else {
                $finalContent = str_replace('{{name}}', $to, offensiveMsgBlock($message));
            }

            if (isset($request->groupIDContact[$to])) {
                $finalContent = Contact::replaceWithUnsubscribeLink($finalContent, $request->groupIDContact[$to]);
            }

            $finalContent .= $watermark;

            if ($isAntiBlockEnabled) {
                // Anti Block Strategy to delay the initiated time.
                $cacheKey = "user:: {$user->id}::whatsapp-desktop-gateway::{$deviceIds[$i % $n]}";
                WhatsappLog::delayInitiatedTime($cacheKey, $initiatedTime);
            }

            $messageLogs[] = [
                'whatsapp_id' => $deviceIds[$i % $n],
                'user_id' => $user->id,
                'to' => $to,
                'message' => $finalContent,
                'gateway' => WhatsappLog::GATEWAY_DESKTOP,
                'initiated_time' => $initiatedTime->clone(),
                'status' => $request->schedule,
                'schedule_status' => $request->schedule,
                'auto_delete' => (bool)$user->auto_delete_whatsapp_pc_messages,
                'document' => $request->document,
                'audio' => $request->audio,
                'image' => $request->image,
                'video' => $request->video,
                'file_caption' => $request->file_caption,
                'created_at' => now(),
                'updated_at' => now(),
                'batch_id' => $batchId
            ];

            $i++;
        }


        WhatsappLog::query()->insert($messageLogs);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => "success",
                'message' => "New WhatsApp Message request sent, view the WhatsApp Log History for final status",
            ]);
        }
        $notify[] = ['success', 'New WhatsApp Message request sent, please see in the WhatsApp Log history for final status'];
        return back()->withNotify($notify);
    }

    public function createGateway()
    {
        $title = "WhatsApp Desktop Gateway Settings";

        $whatsAppDevices = UserWindowsToken::query()->where('user_id', auth()->id())->latest()->paginate(paginateNumber());

        return view('user.whatsapp_desktop_device.create', compact('title', 'whatsAppDevices'));
    }

    public function deleteGateway()
    {
        $device = UserWindowsToken::findOrFail(request('id'));

        if ($device->user_id != \auth()->id()) abort(403);

        $device->delete();

        return back()->withNotify([['success', 'Successfully Deleted the Windows device. No longer able to use this device.']]);
    }

    public function updateMessageGateway(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'gateway' => ['required', Rule::exists('user_windows_tokens', 'id')
                ->where('user_id', \auth()->id())]
        ], $request->all());


        try {
            $gateway = UserWindowsToken::query()->findOrFail($request->gateway);

            if (WhatsappLog::query()
                ->where('user_id', \auth()->id())
                ->whereIn('id', explode(',', $request->id))
                ->where('status', WhatsappLog::PENDING)
                ->update(['whatsapp_id' => $gateway->id])) {
                $notify[] = ['success', "Successfully Whatsapp Desktop Gateway changed"];
            } else {
                $notify[] = ['error', "Gateway not changed. Only Pending messages gateway can be changed. "];
            }

        } catch (\Exception $e) {
            $notify[] = ['error', "Error occurred when changing the gateway."];
        }

        return back()->withNotify($notify);

    }

    public function pauseCampaign()
    {
        $campaignIds = explode(',', \request('campaign_id'));

        $logs = WhatsappLog::query()->where('user_id', \auth()->id())
            ->whereIn('status', [WhatsappLog::PENDING, WhatsappLog::PAUSE])
            ->whereIn('batch_id', $campaignIds)
            ->get();

        foreach ($logs as $log) {
            if ($log->status == WhatsappLog::PENDING) {
                $log->update([
                    'status' => WhatsappLog::PAUSE
                ]);
            } else {
                $log->update([
                    'status' => WhatsappLog::PENDING
                ]);
            }
        }

        $notify[] = ['success', "Successfully Paused/UnPaused the Whatsapp Campaigns."];
        return back()->withNotify($notify);
    }
}
