<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsappBotUpdateRequest;
use App\Models\AiBot;
use App\Models\WhatsappBot;
use App\Models\WhatsappDevice;

class BotController extends Controller
{
    public function index()
    {
        $title = 'Manage WhatsApp Bot';

        $aiBots = AiBot::query()->where('user_id' , auth()->id())->get();
        $whatsappGateways = WhatsappDevice::connected(auth()->id())->get();
        $whatsappBot = WhatsappBot::query()->firstOrCreate(['user_id' => auth()->id()] , [
            'ai_bot_id' => AiBot::userBot(auth()->id())->id
        ]);

        return view('whatsapp.bots.index' , compact('title' , 'whatsappBot' , 'whatsappGateways' , 'aiBots'));
    }

    public function update(WhatsappBotUpdateRequest $request)
    {
        $whatsappBot = WhatsappBot::query()->firstOrCreate(['user_id' => auth()->id()]);

        $data = $request->only(['whatsapp_gateway_id' , 'ai_bot_id' , 'is_enabled', 'handle_only_unknown_user']);
        $data['is_enabled'] = (bool)($data['is_enabled'] ?? false);
        $data['handle_only_unknown_user'] = (bool)($data['handle_only_unknown_user'] ?? false);
        $data['data'] = [
            'ignored_numbers' => $request->get('ignored_numbers') ,
            'allowed_numbers' => $request->get('allowed_numbers') ,
            'greetings_text' => $request->get('greetings_text')
        ];

        $whatsappBot->update($data);

        return back()->withNotify([['success' , 'Successfully updated whatsapp bot']]);
    }
}
