<?php

namespace App\Http\Controllers;

use App\Http\Requests\BotShareGenerateRequest;
use App\Models\AiBot;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class ChatShareController extends Controller
{
    public function shareLink()
    {
        $title = 'Share Link';

        $aiBot = AiBot::userBot(auth()->id());
        $user = Auth::guard('web')->user();
        if ($user) {
            $token = $user->createToken('share-link', [User::ABILITY_WEB_BOT]);
        } else {
            $token = '';
        }


        if (!$aiBot) return to_route(auth()->id() ? 'user.ai_bots.index' : 'admin.ai_bots.index')->withNotify([['error', 'Configure your BOT']]);

        $shareLink = $aiBot->share_name ? route('ai_bots.public.chat', ['user' => $aiBot->share_name]) : null;
        $greetingsText = $aiBot->data['greetings_text'] ?? '';

        return view('ai-bots.share', compact('title', 'shareLink', 'greetingsText', 'token'));
    }

    public function chatView($user)
    {
        $aiBot = AiBot::query()
            ->where('share_name', $user)
            ->firstOrFail();

        session()->put('public_user', $user);

        $adminBusinessInfo = AiBot::userBot(null)?->data['business_text'] ?? null;

        return view('ai-bots.web-chat', compact('aiBot', 'adminBusinessInfo'));
    }

    public function generateShareName(BotShareGenerateRequest $request)
    {
        $aiBot = AiBot::userBot(auth()->id());
        $aiBot->share_name = $request->share_name;
        $aiBot->saveOrFail();

        return back()->withNotify([['success', 'Successfully Generated Share Name.']]);
    }

    public function updateGreetingsText()
    {
        $greetingsText = request('greetings_text');

        if (!$greetingsText) return back()->withNotify([['error', 'Invalid Greetings Text.']]);

        $aiBot = AiBot::userBot(auth()->id());
        $aiBot->data['greetings_text'] = $greetingsText;
        $aiBot->saveOrFail();

        return back()->withNotify([['success', 'Successfully updated greetings text.']]);
    }
}
