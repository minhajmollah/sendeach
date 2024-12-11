<?php

namespace App\Listeners;

use App\Events\NewAiChatResponse;
use App\Models\BotCustomReplies;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;

class AddAiResponseToCustomReply implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\NewAiChatResponse $event
     * @return void
     */
    public function handle(NewAiChatResponse $event)
    {

        $aiBotResponse = $event->aiBotResponse;

        $reply = Arr::get(Arr::first($aiBotResponse->choices , function ($choice) {
            return Arr::get($choice , 'message.role') == 'assistant';
        }) , 'message.content');

        if (!$reply) return;

        $keywords = $aiBotResponse->aiBot->getKeywords("User: $aiBotResponse->message \n Bot: $reply");

        if (!$keywords) return;

        if (is_string($keywords[0])) {
            $keywords = [$keywords];
        }

        BotCustomReplies::query()->create([
            'message' => $aiBotResponse->message ,
            'reply' => $reply ,
            'ai_bot_response_id' => $aiBotResponse->id ,
            'ai_bot_id' => $aiBotResponse->bot_id ,
            'user_id' => $aiBotResponse->aiBot?->user_id,
            'keywords' => join(' , ' , array_map(fn($key) => strtolower(trim($key)) , $keywords[0] ?? []))
        ]);
    }
}
