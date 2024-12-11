<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiBotBusinessUpdateRequest;
use App\Http\Requests\AiBotUpdateRequest;
use App\Http\Requests\MessageSendRequest;
use App\Models\AiBot;
use App\Models\BotCustomReplies;
use App\Models\Chat;
use App\Models\WhatsappDevice;
use App\Services\PaLMAPIService\TextService;
use Graby\Graby;
use Illuminate\Support\Arr;

class AiBotController extends Controller
{
    public function index()
    {
        $title = 'OpenAI Configuration';

        $name = request('model') ?: \App\Models\AiBot::CHAT;

        $aiBot = AiBot::firstOrCreateModel(auth()->id(), $name);

        $aiBot->updateFineTuneStatus();

        $aiBot->chargeCredits();

        $totalConversations = Chat::query()
            ->join('chat_conversations', 'chats.conversation_id',
                '=', 'chat_conversations.id')
            ->where('ai_bot_id', '=', $aiBot->id)->count();

        $whatsappGateways = WhatsappDevice::connected(auth()->id())->get();

        $availableTrailTokens = $aiBot->data['openai']['available_tokens'] ?? null;

        return view('ai-bots.index', compact('title', 'aiBot', 'name', 'totalConversations', 'whatsappGateways', 'availableTrailTokens'));
    }

    public function updateBusinessInformation(AiBotBusinessUpdateRequest $request)
    {
        $aiBot = AiBot::query()->where(['user_id' => auth()->id(), 'name' => $request->name])->firstOrFail();

        if ($aiBot->update([
            'is_enabled' => (bool)($request->is_enabled),
            'data' => array_merge((array)$aiBot->data, [
                'website' => $request->website,
                'business_text' => $request->business_text,
                'greetings_text' => $request->greetings_text,
                'palm_api' => [
                    'is_enabled' => (bool)$request->is_palm_enabled
                ]
            ])
        ])) {
            $notify = [['success', 'Successfully Created Your AI BOT.']];
        } else {
            $notify = [['error', 'Unable to update model']];
        }

        if (strlen($request->business_text) > 30 && BotCustomReplies::query()->where('user_id', auth()->id())->doesntExist()) {

            if (Arr::get($aiBot->data, 'palm_api.is_enabled')) {

                TextService::generateCustomAutoReplies($request->business_text, auth()->id());
            } else {

                BotCustomReplies::generateCustomAutoReplies($request->business_text, auth()->id());
            }
        }

        return back()->withNotify($notify);
    }

    public function update(AiBotUpdateRequest $request)
    {
        $aiBot = AiBot::query()->where(['user_id' => auth()->id(), 'name' => $request->name])->firstOrFail();

        $data = $request->validated();
        $data['name'] = $request->name;

        $n = $request->train_data['prompt'] ?? [];

        for ($i = 0; $i < count($n); $i++) {
            if (($completion = Arr::get($request->train_data, 'completion.' . $i)) && ($prompt = Arr::get($request->train_data, 'prompt.' . $i))) {
                $data['train_data'][] = [
                    'prompt' => $prompt,
                    'completion' => $completion,
                ];
            }
        }

        $data['enable_memory'] = (bool)($data['enable_memory'] ?? false);

        $notify = $aiBot->updateModel($data)
            ? [['success', 'Successfully updated AI Bot Prompt']]
            : [['error', 'Unable to update model']];

        return back()->withNotify($notify);
    }

    public function advancedSettings()
    {
        $title = 'OpenAI Advanced Configuration';

        $name = request('model') ?: \App\Models\AiBot::CHAT;

        $aiBot = AiBot::firstOrCreateModel(auth()->id(), $name);

        $aiBot->updateFineTuneStatus();

        $aiBot->chargeCredits();

        return view('ai-bots.advanced-configurations', compact('title', 'aiBot', 'name'));
    }

    public function cancelFineTune()
    {
        $aiBot = AiBot::query()->where(['user_id' => auth()->id(), 'name' => AiBot::FINE_TUNE])->firstOrFail();

        if ($aiBot->cancelFineTune()) {
            $notify = [['success', 'Successfully cancelled Fine Tune Training']];
        } else {
            $notify = [['error', 'Unable to Cancel the training']];
        }

        return back()->withNotify($notify);
    }

    public function getChatsAsFineTuneDataSet()
    {
        return AiBot::find(9)->getUserChatsAsFineTuneData();
    }

    public function parseBusinessDetails()
    {
        $graby = new Graby();
        $result = $graby->fetchContent(request('website'))->getHtml();
        $businessInfo = strip_tags($result);

        $businessInfo = trim(preg_replace('/\s\s+/', ' ', $businessInfo));
        $businessInfo = trim(preg_replace("/&#?[a-z0-9]+;/i", "", $businessInfo));

        $aiBot = AiBot::query()->where('user_id', auth()->id())
            ->where('name', AiBot::CHAT)->firstOrFail();

        if (Arr::get($aiBot->data, 'palm_api.is_enabled') || !$aiBot->chargeCredits()) {
            // Bard Summarizations
            $businessInfo = TextService::summarize($businessInfo);
        } else {
            // OpenAI Summarizations
            if ($businessInfo && $aiBot->chargeCredits() && strlen($businessInfo) < 2000 && strlen($businessInfo) > 50) {
                $businessInfo = $aiBot->summarizeText($businessInfo);
            }
        }

        return response()->json([
            'content' => $businessInfo
        ]);
    }

    public function summarizeBusinessDetails()
    {
        $businessInfo = request('business_text');

        $aiBot = AiBot::userBot(auth()->id());

        if ($businessInfo) {
            if (Arr::get($aiBot->data, 'palm_api.is_enabled') || !$aiBot->chargeCredits()) {
                $businessInfo = $aiBot->summarizeText($businessInfo);
            } else {
                $businessInfo = TextService::summarize($businessInfo);
            }
        }

        return response()->json([
            'content' => $businessInfo
        ]);
    }

    public function enablePIAI()
    {
        $whatsappGateway = WhatsappDevice::connected(auth()->id())
            ->where('id', request('whatsapp_gateway_id'))->firstOrFail();

        $aiBot = AiBot::userBot(auth()->id());

        $aiBot->data['pi_ai'] = [
            'is_enabled' => true,
            'whatsapp_gateway_id' => $whatsappGateway
        ];

        if ($aiBot->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully enabled Inflection AI'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to enable/disable the Inflection AI.'
        ]);
    }

    public function disablePIAI()
    {
        $aiBot = AiBot::userBot(auth()->id());

        $aiBot->data['pi_ai'] = [
            'is_enabled' => false,
            'whatsapp_device_id' => null
        ];

        if ($aiBot->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully disabled Inflection AI'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to enable/disable the Inflection AI.'
        ]);
    }

    public function systemSettings()
    {
        $title = 'OpenAI System Configuration';

        $aiBot = AiBot::adminChat();

        return view('ai-bots.system-configurations', compact('title', 'aiBot'));
    }

    public function updateSystemSettings()
    {
        $data = request()->validate([
            'openai_api_key' => 'nullable',
            'is_user_trial_enabled' => 'nullable',
            'openai_trial_tokens_per_user' => 'nullable|integer'
        ]);

        $aiBot = AiBot::adminChat();

        $aiBot->data['openai'] = [
            'api_key' => $data['openai_api_key'] ?? null,
            'is_user_trial_enabled' => (bool)($data['is_user_trial_enabled'] ?? null),
            'trial_tokens_per_user' => $data['openai_trial_tokens_per_user'] ?? null,
        ];

        $aiBot->saveOrFail();

        return back()->withNotify([['success', 'Updated System Settings.']]);
    }

    public function spinMessage(MessageSendRequest $request)
    {
        $request->handle();

        $aiBot = AiBot::userBot(auth()->id());

        $totalRecipients = count($request->to);

        $message = request('message');
        $limit = $request->get('limit');

        $messages = $this->spinMessages($totalRecipients, $limit, $aiBot, $message);

        return response()->json(['messages' => $messages, 'total' => $totalRecipients, 'message' => $message]);
    }

    public function spinMessages(int $totalRecipients, mixed $limit, ?AiBot $aiBot, string $message, $messageLength = null): array
    {
        $totalSpins = 0;
        $messages = [];

        if ($totalRecipients >= 30) {
            $totalSpins = 5;
        }

        if ($limit && $limit < $totalSpins) {
            $totalSpins = min($limit, 10);
        }

        for ($i = 0; $i < $totalSpins; $i++) {
            $message = $aiBot->spinMessage($message, $messageLength);
            $messages[] = $message;
        }
        return $messages;
    }
}
