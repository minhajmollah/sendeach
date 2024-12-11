<?php

namespace App\Listeners;

use App\Events\MessengerWebhookReceived;
use App\Models\AiBot;
use App\Models\Chat;
use App\Models\ChatConversation;
use App\Models\FacebookMessenger;
use App\Services\FacebookMessengerService;
use Illuminate\Support\Arr;

class HandleMessengerWebhook
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
     * @param MessengerWebhookReceived $webhookEvent
     * @return void
     * @throws \Exception
     */
    public function handle(MessengerWebhookReceived $webhookEvent)
    {
        foreach (($webhookEvent->request['entry'] ?? []) as $event) {

            /** @var FacebookMessenger $facebookMessenger */
            $facebookMessenger = FacebookMessenger::query()->where([
                'page_id' => $event['id'] ,
            ])->firstOrFail();

            $aiBot = $facebookMessenger->aiBot ?: AiBot::userBot($facebookMessenger->user_id);

            if (!$aiBot) {
                throw new \Exception('No Ai Bot Configured');
            }

            $messengerService = new FacebookMessengerService($facebookMessenger);

            foreach ($event['messaging'] as $event) {

                $sender = $facebookMessenger->senders()->firstOrCreate([
                    'psid' => $event['sender']['id']
                ]);

                $conversation = ChatConversation::startConversationByparticipants($sender , $aiBot);

                if (Arr::has($event , 'message')) {

                    $messengerService->senderAction($sender->psid , 'mark_seen');
                    $messengerService->senderAction($sender->psid , 'typing_on');

                    $this->handleMessageEvent($conversation , $event , $messengerService , $facebookMessenger);

                } elseif ($delivery = Arr::get($event , 'delivery')) {

                    $this->handleDeliveryEvent($conversation , $delivery);

                } else if ($read = Arr::get($event , 'read')) {

                    $this->handleReadEvent($conversation);

                }
            }
        }
    }

    private function handleMessageEvent(
        ChatConversation         $conversation ,
        array                    $event ,
        FacebookMessengerService $messengerService ,
        FacebookMessenger        $facebookMessenger
    )
    {
        $message = Arr::get($event , 'message');

        $existingMessage = $conversation->chats()
            ->where('is_sender' , false)
            ->where('messenger_message_id' , $message['mid'])->first();

        if (($text = Arr::get($message , 'text')) && !$existingMessage) {

            if ($conversation->chats()->doesntExist() && ($greetingsText = $conversation->aiBot->getGreetingsText())) {
                $messengerService->replyMessage($greetingsText , $event['sender']['id']);
            }

            $chat = $conversation->chatWithAi($text , attr: ['messenger_message_id' => $message['mid']]);

            $reply = $chat?->message;

            if ((!$reply || $reply == 'Error') && $conversation->chats()->doesntExist()) {
                $reply = 'Thanks for contacting us. we will get back to you soon.';
            }

            if ($reply) {
                $messengerService->replyMessage($reply , $event['sender']['id']);
            }
        }
    }

    private function handleReadEvent(ChatConversation $conversation)
    {
        $conversation->chats()
            ->where('is_sender' , false)
            ->update(['status' => Chat::STATUS_READ]);
    }

    private function handleDeliveryEvent(ChatConversation $conversation , $delivery)
    {
        $conversation->chats()
            ->where('is_sender' , false)
            ->whereIn('messenger_message_id' , $delivery['mids'])
            ->update(['status' => Chat::STATUS_DELIVERED]);
    }

    public function handlePostBackEvent()
    {

    }
}
