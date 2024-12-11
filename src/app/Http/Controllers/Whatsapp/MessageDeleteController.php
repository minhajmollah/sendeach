<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\WhatsappDevice;
use App\Models\WhatsappPCMessageDelete;
use App\Services\WhatsappService\WebApiService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageDeleteController extends Controller
{
    public function deleteMessageView()
    {
        $title = 'Delete Whatsapp Messages';

        $keywords = WhatsappPCMessageDelete::query()->where('user_id' , \auth()->id())->latest()->paginate(paginateNumber());

        $webDevices = WhatsappDevice::connected(auth()->id())->get();

        return view('whatsapp.delete' , compact('title' , 'keywords' , 'webDevices'));
    }

    public function searchMessageViaKeywords()
    {
        $data = Validator::validate(request()->all() , [
            'keywords' => ['required' , 'string'] ,
            'is_exact' => ['nullable'] ,
            'whatsapp_device' => ['required' , Rule::exists('wa_device' , 'id')
                ->where('status' , 'connected')->where('user_id' , auth()->id())]
        ]);

        $user = \auth()->user();

//        if ($user->credit < 1) {
//            return redirect()->route('user.credits.create')->withNotify([['error' , 'Please Buy some credits to delete whatsapp Messages.']]);
//        }

        $whatsappDevice = WhatsappDevice::find($data['whatsapp_device']);

        $messages = WebApiService::searchMessages($whatsappDevice , $data['keywords'], isExact: (bool)($data['is_exact'] ?? null));

        return back()->with('messages' , $messages)->withInput();
    }

    public function deleteMessages()
    {
        $data = Validator::validate(request()->all() , [
            'messageIds' => ['required'] ,
            'chatIds' => ['required'] ,
            'whatsapp_device' => ['required' , Rule::exists('wa_device' , 'id')
                ->where('status' , 'connected')->where('user_id' , auth()->id())]
        ]);

//        if ($user->credit < 1) {
//            return redirect()->route('user.credits.create')->withNotify([['error' , 'Please Buy some credits to delete whatsapp Messages.']]);
//        }

        $messageIds = explode(',' , $data['messageIds']);
        $chatIds = explode(',' , $data['chatIds']);

        if (count($messageIds) != count($chatIds)) {
            return back()->withNotify([['error' , 'Something went wrong.']]);
        }

        $n = count($messageIds);

        $whatsappDevice = WhatsappDevice::find($data['whatsapp_device']);

        for ($i = 0; $i < $n; $i++) {
            WebApiService::deleteMessage($whatsappDevice , $messageIds[$i] , $chatIds[$i]);
        }

        return back()->withNotify([['success' , 'Successfully Sent Message Delete Request to Web Gateway. The Messages will be deleted soon.']]);
    }

    public function toggleAutoDelete()
    {

        $user = \auth()->user();

//        if (!$user->auto_delete_whatsapp_pc_messages && $user->credit < 1) {
//            return redirect()->route('user.credits.create')->withNotify([['error' , 'Please Buy some credits to enable auto delete whatsapp messages']]);
//        }

        if (WhatsappDevice::connected(auth()->id())->doesntExist()) {
            return back()->withNotify([['error' , 'Please connect your whatsapp Device to Whatsapp Web Gateway start using this feature.']]);
        }

        if (!$user->auto_delete_whatsapp_pc_messages) {
            $user->auto_delete_whatsapp_pc_messages = now();
        } else {
            $user->auto_delete_whatsapp_pc_messages = null;
        }

        $user->saveOrFail();

        return back()->withNotify([['success' , 'Successfully Enabled Auto Delete Whatsapp Messages.']]);
    }
}
