<?php

namespace App\Services\PaLMAPIService;

use App\Models\BotCustomReplies;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class TextService
{

    const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generateText';

    private static function getBaseUrl(): string
    {
        return self::BASE_URL . '?key=' . config('google_palm.key');
    }

    public static function generateCustomAutoReplies($businessText , $userId)
    {
        $data = [
            'prompt' => [
                'text' => 'information about my business is given in triple quotes.  I"m creating a chatbot about my business. Give me example of 4 keywords as array and a short chatbot reply in json format.
                Example Response: [{"keywords": "qualities of your business , business unique , business apart , business values", "reply": "We promise honesty as our best policy and offer quality Indian products at cheap prices."}]
                \n\n """' . $businessText . '""""'
            ] ,
            'temperature' => 0 ,
        ];

        $response = Http::post(self::getBaseUrl() , $data);

        logger()->debug($response->body());

        $jsonStr = Arr::get($response->json() , 'candidates.0.output');

        $replies = json_decode($jsonStr , true);
        $replies = $replies ?: json_decode(substr($jsonStr , strpos($jsonStr , '[') , -3) , true);

        if ($replies) {
            $replies = array_map(function ($reply) use ($userId) {
                return [
                    'reply' => $reply['reply'] ,
                    'keywords' => is_array($reply['keywords']) ? join(', ' , $reply['keywords']) : (string)$reply['keywords'] ,
                    'user_id' => $userId ,
                    'created_at' => now() ,
                    'updated_at' => now()
                ];
            } , $replies);

            return BotCustomReplies::query()->insert($replies);
        }

        return null;
    }

    public static function summarize($text): ?string
    {
        $data = [
            'prompt' => [
                'text' => "Summarize the given paragraph.
                \n\nParagraph: $text"
            ] ,
            'temperature' => 0.5 ,
        ];

        $response = Http::post(self::getBaseUrl() , $data);

        !Arr::get($response->json() , 'candidates.0.output') ? dd($response->json()) : null;

        return $response->successful() ? Arr::get($response->json() , 'candidates.0.output') : null;
    }
}
