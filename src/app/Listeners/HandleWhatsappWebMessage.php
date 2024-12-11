<?php

namespace App\Listeners;

use App\Events\NewWhatsappWebMessage;
use App\Models\AiBot;
use App\Models\ChatConversation;
use App\Models\WhatsappSender;
use App\Services\WhatsappService\WebApiService;
use Illuminate\Support\Arr;

class HandleWhatsappWebMessage
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
     * @param \App\Events\NewWhatsappWebMessage $event
     * @return void
     */
    public function handle(NewWhatsappWebMessage $event)
    {
        $message = $event->message;
        $whatsappBot = $event->whatsappDevice?->whatsappBot;
        $body = $message['body'] ?? null;

        if (!$body || !Arr::has($message , 'from') || !$whatsappBot
            || !$whatsappBot->is_enabled || !$whatsappBot->aiBot) {
            return;
        }

        $chatId = Arr::get($message , 'from');
        $from = explode('@' , $chatId)[0];


//        if ($from == config('ai_bot.pi_ai_whatsapp_number')) {
//            return $this->handlePIAIWhatsappReply($body);
//        }

        // Check if number is in contact
        $contact = WebApiService::getContactClassInfo($event->whatsappDevice , $chatId);
        if ($whatsappBot->handle_only_unknown_user && Arr::get($contact , 'isMyContact')) {
            return;
        }

        // Check Ignored Numbers
        if (in_array($from , $whatsappBot->data['ignored_numbers'] ?? [])) {
            return;
        }

        // Check if number is allowed
        if (isset($whatsappBot->data['allowed_numbers']) &&
            !in_array($from , $whatsappBot->data['allowed_numbers'])) {
            return;
        }

        $whatsappSender = WhatsappSender::query()->firstOrCreate(['phone' => $from] , [
            'name' => $message['_data']['notifyName'] ?? ''
        ]);

        $conversation = ChatConversation::startConversationByparticipants($whatsappSender , $whatsappBot->aiBot
            ?: AiBot::userBot($whatsappBot->user_id));

        $greetingText = $conversation->aiBot->getGreetingsText();

        if ($conversation->chats()->doesntExist() && $greetingText) {
            WebApiService::send($event->whatsappDevice , $chatId , $greetingText);
        }

        $reply = $conversation->chatWithAi($body ,
            attr: ['messenger_message_id' => $message['id']['id'] ?? '']);

        if ($reply) {
            WebApiService::send($event->whatsappDevice , $chatId , $reply->message);
        }
    }

    private function handlePIAIWhatsappReply(mixed $body)
    {
        return null;
    }
}
