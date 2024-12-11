<?php

namespace App\Http\Controllers\User\Whatsapp;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageSendRequest;
use App\Jobs\ProcessBulkWhatsapp;
use App\Jobs\ProcessWhatsapp;
use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\Group;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    use WhatsappControllerTrait;

    public function create()
    {
        $title = "Compose Message";
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $groups = $user->group()->where('type', '<>', Group::TYPE_SYSTEM)->get();
        $templates = $user->template()->get();

        $devices = WhatsappDevice::where('user_type', 'user')->where('user_id', $user->id)->where('status', 'connected')->orderBy('name')->get();
        if (count($devices) < 1) {
            $notify[] = ['error', 'Please connect at least one device to send messages!'];
            return redirect()->route('user.gateway.whatsapp.create')->withNotify($notify);
        }
        $availableCredits = (int)$user->credit;

        $availableDepositAmount = round($user->getAvailableDepositInDollars(), 2);

//        if ($credits === 0) {
//            $notify[] = ['error' , "Your credit balance is zero. Please purchase a new plan to continue sending messages!"];
//            return redirect()->route('user.plan.create')->withNotify($notify);
//        }
//        if ($dollars <= 5) {
//            $notify[] = ['info' , "You deposit balance is \${$dollars} ({$credits} Credits). Watermarks will be added if credits goes to 0!"];
//            session()->flash('notify' , $notify);
//        }
        return view('user.whatsapp.create', compact('title', 'groups', 'templates', 'devices', 'availableDepositAmount', 'availableCredits'));
    }

    public function send(MessageSendRequest $request)
    {
        $request->handle();

        $user = Auth::user();

        $whatsappGateway = WhatsappDevice::query()->where('user_type', 'user')
            ->where('user_id', $user->id)->where('status', 'connected');

        if ($request->whatsapp_device) {
            $whatsappGateway = $whatsappGateway->where('id', $request->whatsapp_device);
        }

        $whatsappGateway = $whatsappGateway->get(['delay_time', 'id'])->toArray();

        if (count($whatsappGateway) < 1) {
            $notify[] = ['error', 'No available WhatsApp Gateway'];
            return back()->withInput()->withNotify($notify);
        }

        if(count($request->to) > 50)
        {
            $notify[] = ['error', 'A maximum of 50 WhatsApp messages can be transmitted per sending session via the web gateway.'];
            return back()->withInput()->withNotify($notify);
        }

        $request->getAttachment();

        $data['schedule_date'] = $request->schedule_date ?: now();
        $data['to'] = $request->to;
        $data['numberGroupName'] = $request->numberGroupName;
        $data['groupIDContact'] = $request->groupIDContact;
        $data['schedule'] = $request->schedule;
        $data['audio'] = $request->audio;
        $data['image'] = $request->image;
        $data['document'] = $request->document;
        $data['video'] = $request->video;

        $data['message'] = [$request->message];

        if ($spinMessage = $request->spinMessage) {
            $data['message'] = array_merge($data['message'], $spinMessage);
        }

        ProcessBulkWhatsapp::dispatch($user->id, $whatsappGateway, $data)->onQueue('whatsapp');

        $notify[] = ['success', 'New WhatsApp Message request sent, please see in the WhatsApp Log history for final status'];
        return back()->withNotify($notify);
    }
}

