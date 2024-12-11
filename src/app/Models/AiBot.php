<?php

namespace App\Models;

use App\Jobs\TrainFineTuneModel;
use App\Services\PaLMAPIService\MessageService;
use App\Services\RailwayBardService;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use OpenAI\Client;

/**
 * @property array $data Never Override this data or replace it.
 * @property int $total_tokens_used
 * @property int $id
 * @property int|null $user_id
 * @property string $model
 * @property ?float $temperature
 */
class AiBot extends Model
{
    use HasFactory;

    const CHAT = 'chat';
    const FINE_TUNE = 'fine-tune';
    const PALM = 'palm';

    const CHAT_GPT_35 = 'gpt-3.5-turbo';
    const FINE_TUNE_ADA = 'ada';
    const BARD_UNOFFICIAL_API = 'unofficial-bard-api';

    const BOT_NAMES = [self::CHAT, self::FINE_TUNE];
    const FINE_TUNE_MODELS = [self::FINE_TUNE_ADA];
    const CHAT_MODELS = [self::CHAT_GPT_35, self::BARD_UNOFFICIAL_API];

    const AI_NAME = [
        self::FINE_TUNE => 'Fine Tune Model',
        self::CHAT => 'Chat Model',
    ];

    const AI_MODELS = [
        self::FINE_TUNE => self::FINE_TUNE_MODELS,
        self::CHAT => self::CHAT_MODELS,
    ];

    protected $casts = ['data' => AsArrayObject::class];

    protected $fillable = [
        'max_tokens', 'user_id', 'total_tokens_used', 'messages_per_minute', 'temperature', 'n',
        'name', 'model', 'data', 'price_per_1000_tokens', 'enable_memory', 'is_enabled'];

    public static function adminCompletion()
    {
        return static::query()
            ->where('user_id', null)
            ->where('name', static::FINE_TUNE)
            ->first();
    }

    public static function adminChat()
    {
        return static::query()
            ->where('user_id', null)
            ->where('name', AiBot::CHAT)
            ->first();
    }


    public static function firstOrCreateModel($userId, $name, $data = []): Model|AiBot
    {

        $aiBot = AiBot::query()->where([
            'name' => $name,
            'user_id' => $userId,
        ])->first();

        if ($aiBot) return $aiBot;

        $name = $name ?: static::CHAT;
        $model = $data['model'] ?? static::AI_MODELS[$name][0];

        $data = [];
        $data['greetings_text'] = config('openai.greetings_text');

        if ($name == static::CHAT) {
            $data = [
                'system_text' => config('openai.chat.default_system_text'),
                'assistant_text' => config('openai.chat.default_assistant_text'),
                'openai' => [
                    'available_tokens' => AiBot::adminChat()->data['openai']['trial_tokens_per_user'] ?? 0
                ]
            ];
        }

        return AiBot::query()->create([
            'name' => $name,
            'user_id' => $userId,
            'model' => $model,
            'data' => $data,
            'price_per_1000_tokens' => config('openai.price_per_1000_tokens')[$model]
        ]);
    }

    public static function userBot($id): ?AiBot
    {
        $bot = static::query()->where('user_id', $id)
            ->where(fn($q) => $q->where('is_default_use', true)
                ->orWhere('name', self::CHAT))->first();

        if (!$bot) {
            $bot = static::firstOrCreateModel($id, self::CHAT);
        }

        return $bot;
    }

    public function updateModel($data): bool|int
    {
        return match ($data['name']) {
            static::CHAT => $this->updateChatModel($data),
            static::FINE_TUNE => $this->updateFineModel($data)
        };
    }

    public function updateFineModel($data): bool
    {
        $data['model'] = $data['model'] ?? static::FINE_TUNE_ADA;

        $data['train_data'] = $data['train_data'] ?? [];

        $toTrain = $data['train_data'] && $data['train_data'] != ($this->data['train_data'] ?? []);

        $this->fill([
            'model' => $data['model'],
            'temperature' => $data['temperature'] ?? 1,
            'messages_per_minute' => $data['messages_per_minute'] ?? 60,
            'n' => $data['n'] ?? 1,
            'max_tokens' => $data['max_tokens'] ?? 1,
            'is_enabled' => $data['is_enabled'],
        ]);

        if ($toTrain && Arr::get($this->data, 'fine_tuned_model_status') != 'pending') {

            $this->data['train_data'] = $data['train_data'];

            // Archive Old Model
            if ($old_fine_tune_id = Arr::get($this->data, 'fine_tune_id')) {
                $this->data['fine_tuned_models_archive'][] = [
                    'fine_tune_id' => $old_fine_tune_id,
                    'fine_tuned_model_status' => Arr::get($this->data, 'fine_tuned_model_status'),
                    'fine_tuned_model' => Arr::get($this->data, 'fine_tuned_model'),
                ];
            }

            $this->data['fine_tuned_model_status'] = 'waiting';
            $this->data['fine_tuned_model_stream_message'] = 'Waiting to Train to Job';
            $this->data['fine_tune_id'] = null;
            $this->data['fine_tuned_model'] = null;

            $this->save();

            TrainFineTuneModel::dispatch($this);
        } else {
            return $this->save();
        }

        return true;
    }

    public function updateChatModel($data): int
    {
        return $this->update([
            'data' => array_merge((array)$this->data, [
                'user_text' => $data['user_text'] ?? '',
                'system_text' => $data['system_text'] ?? '',
                'assistant_text' => $data['assistant_text'] ?? '',
                'stop' => $data['stop'] ?? '',
            ]),
            'model' => $data['model'] ?? static::CHAT_GPT_35,
            'temperature' => $data['temperature'] ?? 1,
            'messages_per_minute' => $data['messages_per_minute'] ?? 60,
            'n' => $data['n'] ?? 1,
            'max_tokens' => $data['max_tokens'] ?? 1,
            'enable_memory' => $data['enable_memory'],
        ]);
    }

    public function chat($message, $conversationId): string|null
    {
        if (!$this->chargeCredits()) {
            return null;
        }

        return match ($this->name) {
            AiBot::CHAT => $this->chatModels($message, $conversationId),
            AiBot::FINE_TUNE => $this->completion($message, $conversationId)
        };
    }

    public function chatModels($message, $conversationId): string|null
    {
        $reply = null;
//
        if (Arr::get($this->data, 'palm_api.is_enabled')) {
            $messageService = (new MessageService($this));

            $reply = $messageService->message($message, $conversationId);
        }


        if (!$reply && $this->is_enabled) {
            return $this->chatGPT($message, $conversationId);
        }

        return $reply;
    }

    public function completion($message): string|null
    {
        try {
            if (!($model = Arr::get($this->data, 'fine_tuned_model'))) {
                return null;
            }

            $data = [
                'model' => $model,
                'prompt' => $message,
                'max_tokens' => $this->max_tokens ?: 10,
                'temperature' => $this->temperature ?: 1,
                'n' => $this->n ?: 1,
                'user' => $this->user_id ?: 'Admin',
            ];

            $result = self::getOpenAIClient()->completions()->create($data);

            $this->total_tokens_used += $result->usage->totalTokens;
            $this->save();

            $aiResponse = AiBotResponse::query()->create([
                'bot_id' => $this->id,
                'total_tokens_used' => $result->usage->totalTokens,
                'temperature' => $this->temperature,
                'choices' => $result->choices,
                'message' => $message,
            ]);

        } catch (\Exception $exception) {
            logger()->error($exception);

            return null;
        }

        return $aiResponse;
    }

    public function chatGPT($message, $conversationId): ?string
    {
        try {
            $data = [
                'model' => $this->model,
                'max_tokens' => $this->max_tokens,
                'temperature' => $this->temperature,
                'n' => $this->n ?: 1,
                'user' => 'User: ' . $conversationId ?: $this->user?->email ?: 'Admin',
            ];

            if ($systemText = $this->getSystemText()) {
                $data['messages'][] = ['role' => 'system', 'content' => $systemText];
            } else {
                return null;
            }


            if (isset($this->data['assistant_text'])) $data['messages'][] =
                ['role' => 'assistant', 'content' => $this->data['assistant_text']];
            if (isset($this->data['stop'])) $data['stop'] = $this->stop;

            if ($this->enable_memory) {
                $data['messages'] = array_merge($data['messages'], $this->getPreviousChats($conversationId));
            }

            $data['messages'][] = ['role' => 'user', 'content' => $this->setUserMessage($message)];

            $result = self::getOpenAIClient()->chat()->create($data);

            $this->total_tokens_used += $result->usage->totalTokens;
            $this->save();

            logger()->debug(json_encode($result));

            $aiResponse = AiBotResponse::query()->create([
                'bot_id' => $this->id,
                'total_tokens_used' => $result->usage->totalTokens,
                'temperature' => $this->temperature,
                'choices' => $result->choices,
                'message' => $message,
                'reply' => $result->choices[0]->message->content,
                'data' => [
                    'system_text' => $this->data['system_text'] ?? '',
                    'assistant_text' => $this->data['assistant_text'] ?? '',
                    'stop' => $this->data['stop'] ?? '',
                ],
                'chat_conversation_id' => $conversationId,
                'model' => AiBot::CHAT_GPT_35
            ]);

        } catch (\Exception $exception) {
            logger()->error($exception);

            return null;
        }

        return $result->choices[0]->message->content;
    }

    public function summarizeText($text)
    {

        if (!$this->chargeCredits()) {
            return null;
        }

        try {
            $data = [
                'model' => $this->model,
                'temperature' => $this->temperature,
                'n' => $this->n ?: 1,
                'user' => 'User: ' . $this->user?->email ?: 'Admin',
                'messages' => [
                    ['role' => 'user', 'content' => "Condense the text delimited by triple quotes
                     \n'''$text''''"]
                ]
            ];

            logger()->debug(json_encode($data));

            $result = self::getOpenAIClient()->chat()->create($data);

            $this->total_tokens_used += $result->usage->totalTokens;
            $this->save();

            logger()->debug(json_encode($result->toArray()));

            AiBotResponse::query()->create([
                'bot_id' => $this->id,
                'total_tokens_used' => $result->usage->totalTokens,
                'temperature' => $this->temperature,
                'choices' => $result->choices,
                'message' => $text,
                'data' => [
                    'system_text' => $this->data['system_text'] ?? '',
                ],
            ]);

        } catch (\Exception $exception) {
            logger()->error(json_encode($exception));

            return null;
        }

        return $result->choices[0]->message->content;
    }

    /**
     * @param $message
     * @return array|mixed|string|string[]
     */
    public function setUserMessage($message): mixed
    {
        $reference = $this->data['reference'] ?? '';
        return str_contains($reference, '{{message}}') ?
            str_replace('{{message}}', $message, $reference)
            : $reference . '\n\n' . $message;
    }

    public function cancelFineTune(): bool
    {
        if ($status = Arr::get($this->data, 'fine_tuned_model_status')) {
            if ($status !== 'cancelled' && $status !== 'succeeded') {

                $this->data['fine_tuned_model_status'] = 'cancelled';
                $this->data['fine_tuned_model_stream_message'] = 'Cancelled Job';

                if (($fine_tune_id = Arr::get($this->data, 'fine_tune_id'))) {
                    $response = self::getOpenAIClient()->fineTunes()->cancel($fine_tune_id);
                    $this->data['fine_tuned_model_status'] = $response->status;
                    $this->data['fine_tuned_model_stream_message'] = 'Cancelled: ' . $response->model;
                }

                return $this->save();
            }
        }

        return false;
    }

    public function updateFineTuneStatus()
    {
        if ($fine_tune_id = Arr::get($this->data, 'fine_tune_id')) {
            $status = Arr::get($this->data, 'fine_tuned_model_status');

            if ($status !== 'cancelled' && $status !== 'succeeded') {
                $response = self::getOpenAIClient()->fineTunes()->retrieve($fine_tune_id);
                $this->data['fine_tuned_model'] = $response->fineTunedModel;
                $this->data['fine_tuned_model_status'] = $response->status;
                $this->save();
            }
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCreditsUsage(): float|int
    {
        $tokensPerDollar = (1000 / ($this->price_per_1000_tokens)) * (1 - (config('openai.tokens_commission') / 100));

        $tokensPerCredit = $tokensPerDollar * CreditLog::getDollarPerCredit();

        return round($this->total_tokens_used / $tokensPerCredit, 4);
    }

    public function chargeCredits(): bool
    {
        if (!$this->user) return true;

        $tokensPerDollar = (1000 / ($this->price_per_1000_tokens)) * (1 - (config('openai.tokens_commission') / 100));

        $tokensPerCredit = $tokensPerDollar * CreditLog::getDollarPerCredit();

        $pendingChargeableTokens = $this->total_tokens_used - $this->charged_tokens;

        $availableUserTokens = Arr::get($this->data, 'openai.available_tokens');

        if ($pendingChargeableTokens && $availableUserTokens) {
            if (($this->data['openai']['available_tokens'] - $pendingChargeableTokens) >= 0) {
                $this->data['openai']['available_tokens'] -= $pendingChargeableTokens;
                $this->charged_tokens += $pendingChargeableTokens;
            } else {
                $this->charged_tokens += $this->data['openai']['available_tokens'];
                $this->data['openai']['available_tokens'] = 0;
            }

            $availableUserTokens = $this->data['openai']['available_tokens'];
            $this->save();
        }

        if ($availableUserTokens > 0) {
            return true;
        } elseif ($this->user->credit < 1) {
            return false;
        }

        if ($pendingChargeableTokens > $tokensPerCredit) {
            $pendingChargeableTokens = $pendingChargeableTokens - $pendingChargeableTokens % $tokensPerCredit;
            $chargeableCredits = $pendingChargeableTokens / $tokensPerCredit;

            if ($this->user->credit < $chargeableCredits) {
                return false;
            }

            $creditInfo = new CreditLog();
            $creditInfo->user_id = $this->user_id;
            $creditInfo->credit_type = "-";
            $creditInfo->credit = $chargeableCredits;
            $creditInfo->trx_number = trxNumber();
            $creditInfo->post_credit = $this->user->credit;
            $creditInfo->details = 'Credits for cut for using OpenAI Services.';
            $creditInfo->save();

            $this->user->credit -= $chargeableCredits;
            $this->user->save();

            $this->charged_tokens += $pendingChargeableTokens;
            $this->save();
        }

        return true;
    }

    public function getUserChatsAsFineTuneData()
    {
        $chats = ChatConversation::query()
            ->where('ai_bot_id', $this->id)
            ->get();

        $chats = Chat::query()
            ->selectRaw('DATE(created_at) AS created_at, message, is_sender')
            ->whereIn('conversation_id', $chats->pluck('id')->toArray())
            ->get();

        $chats = $chats->groupBy('created_at');
        $dataset = [];

        foreach ($chats as $chat) {
            $prompt = "";

            foreach ($chat as $c) {
                if ($c->is_sender) {
                    $prompt .= "Customer: $c->message \n";
                } else {
                    $prompt .= "Agent: $c->message \n";
                }
            }

            $dataset[] = ['prompt' => $prompt, 'completion' => '\n'];
        }

        $chats = Chat::query()->selectRaw('MATCH (message) AGAINST (\'Hi\'), id, message');

        dd($chats->get()->toArray());

        dd($dataset);

        dd(config('app.timezone'), now(), $chats->toArray());
    }

    public function getKeywords($chats)
    {
        if (!$this->chargeCredits()) return null;

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Provide your answer in JSON form. Reply with only the answer in JSON form and include no other commentary:
                 I am creating a keyword reply chatbot. give me at least 4 keywords users could have used to trigger each of the same
                 responses from chatbot in each of the below.'],
                ['role' => 'user', 'content' => '
                    User: What is cloud communications ?
                    Bot: Cloud communications refers to communication services, such as voice and messaging, that are delivered through the internet.
                         It can be used by our business to provide customer support and engagement on our e-commerce platform
                '],
                ['role' => 'assistant', 'content' => '[
                    ["cloud communications", "communication services", "voice and messaging", "internet"]
                    ]
                '],
                ['role' => 'user', 'content' => $chats],
            ],
            'temperature' => $this->temperature,
            'n' => $this->n ?: 1,
            'user' => $this->user?->email ?: 'Admin',
        ];

        logger()->debug(json_encode($data));

        $result = self::getOpenAIClient()->chat()->create($data);

        $this->total_tokens_used += $result->usage->totalTokens;
        $this->save();

        logger()->debug(json_encode($result));

        return json_decode($result->choices[0]->message->content, true);
    }

    private function getPreviousChats($chatConversationId): array
    {
        $responses = AiBotResponse::query()
            ->where('chat_conversation_id', $chatConversationId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $chats = [];

        $totalTokens = 0;

        $responses->each(function ($response) use (&$chats, &$totalTokens) {

            $totalTokens += $response->total_tokens_used;

            if ($totalTokens > 1000) return;

            $chats[] = ['role' => 'user', 'content' => $response->message];
            $chats[] = ['role' => 'assistant', 'content' => Arr::get(Arr::first($response->choices, function ($choice) {
                return Arr::get($choice, 'message.role') == 'assistant';
            }), 'message.content')];
        });

        return array_reverse($chats);
    }

    public function getGreetingsText(): ?string
    {
        if ($greetingText = ($this->data['greetings_text'] ?? null)) {
            $watermark = config('requirements.greetings_text_postfix');
            return $greetingText . "\n\n*$watermark*";
        }

        return null;
    }

    public function getSystemText(): string
    {
        $businessText = $this->data['business_text'] ?? '';
        $systemText = $this->data['system_text'] ?? '';

        return "$systemText \n\n Business Information: ``` $businessText ```";
    }

    public function chatRailwayBard($message, $conversationId): ?string
    {
        return RailwayBardService::chat($message);
    }

    public static function getOpenAIClient(): Client
    {
        $adminBot = self::adminChat();

        $openAiKey = Arr::get($adminBot->data, 'openai.api_key');


        return \OpenAI::factory()
            ->withApiKey($openAiKey ?: config('openai.api_key'))->make();
    }

    public function spinMessage($message, $messageLength = null)
    {
        if (!$this->chargeCredits()) return null;

        if (!$message) return null;

        $limitPrompt = '.';
        if ($messageLength && $messageLength > 0) {
            $limitPrompt = " with less than $messageLength Characters.";
        }else {
            $limitPrompt = " with same or less no of characters.";
        }

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => "Give me another version for the below text$limitPrompt \n\n $message"],
            ],
            'temperature' => 1,
            'n' => $this->n ?: 1,
            'user' => $this->user?->email ?: 'Admin',
        ];

        logger()->debug(json_encode($data));

        $result = self::getOpenAIClient()->chat()->create($data);

        $this->total_tokens_used += $result->usage->totalTokens;
        $this->save();

        logger()->debug(json_encode($result));

        return $result->choices[0]->message->content;
    }
}
