<?php

namespace App\Services\PaLMAPIService;

use App\Models\AiBot;
use App\Models\AiBotResponse;
use App\Models\BotCustomReplies;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;

class MessageService
{

    const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta2/models/chat-bison-001:generateMessage';

    private static function getBaseUrl(): string
    {
        return self::BASE_URL . '?key=' . config('google_palm.key');
    }

    public function __construct(private readonly AiBot $aiBot)
    {
    }

    public function message($message , $chatConversationId): ?string
    {
        $businessText = $this->aiBot->data['business_text'] ?? '';

        $previousChats = $this->getPreviousChats($chatConversationId);

        $previousChats[] = [
            "author" => "0" ,
            "content" => $message
        ];

        $data = [
            "prompt" => [
                "context" => "Business Info: $businessText \n\nYou are a Customer Support Executive of my company.
                 Your response should be related to our business info only. Keep your responses short." ,
                "messages" => $previousChats
            ] ,
            "temperature" => $this->aiBot->temperature ?: 0.1
        ];

        $response = Http::post(self::getBaseUrl() , $data);

        logger()->debug(json_encode($response->json()));

        if ($response->ok() && $reply = Arr::get($response->json() , 'candidates.0.content')) {

            AiBotResponse::query()->create([
                'bot_id' => $this->aiBot->id ,
                'reply' => $reply ,
                'temperature' => $this->aiBot->temperature ,
                'message' => $message ,
                'chat_conversation_id' => $chatConversationId ,
                'model' => AiBot::PALM
            ]);

            return $reply;
        }

        return null;
    }

    private function getPreviousChats($chatConversationId): array
    {
        $responses = AiBotResponse::query()
            ->where('chat_conversation_id' , $chatConversationId)
            ->orderBy('created_at')
            ->where('model' , AiBot::PALM)
            ->get();

        $chats = [];

        $totalTokens = 0;

        $responses->each(function ($response) use (&$chats , &$totalTokens) {

            $totalTokens += $response->total_tokens_used;

            if ($totalTokens > 1000) return;

            $chats[] = ['author' => '0' , 'content' => $response->message];
            $chats[] = ['author' => '1' , 'content' => $response->reply];
        });

        return $chats;
    }
}
