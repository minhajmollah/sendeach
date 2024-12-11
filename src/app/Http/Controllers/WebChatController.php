<?php

namespace App\Http\Controllers;

use App\Models\AiBot;
use App\Models\Chat;
use App\Models\ChatConversation;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class WebChatController extends Controller
{
    public function index()
    {
        $aiBot = $this->getUserBot();

        $conversation = ChatConversation::startConversationByparticipants(session()->getId() , $aiBot);
        $conversation->unreadChatsForUser()->update(['status' => Chat::STATUS_READ]);

        if ($conversation->chats()->doesntExist() && ($aiBot->data['greetings_text'] ?? null)) {
            $conversation->createGreeting($aiBot->data['greetings_text']);
        }

        return $conversation->chats()
            ->selectForAjax()
            ->paginate(25);
    }

    public function sendMessage()
    {
        $message = \request('message');

        if (strlen($message) > 100) {
            return response()->json(['message' => 'Please limit your input message to less than 100 characters.' . strlen($message)] , 422);
        }

        $aiBot = $this->getUserBot();

        $conversation = ChatConversation::startConversationByparticipants(session()->getId() , $aiBot);

        return $conversation->chatWithAi($message , Chat::STATUS_READ);
    }

    public function unreadMessages()
    {
        $aiBot = $this->getUserBot();

        $conversation = ChatConversation::startConversationByparticipants(session()->getId() , $aiBot);

        return $conversation->readUnReadMessages();
    }

    /**
     * @return AiBot
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserBot(): AiBot
    {
        /** @var User $user */
        $token = PersonalAccessToken::findToken(explode('|', request()->header('Authorization'))[1] ?? '');
        if ($token && $token->can(User::ABILITY_WEB_BOT)) {
            $aiBot = AiBot::userBot($token->tokenable->id);

        } elseif (session()->has('public_user')) {

            $aiBot = AiBot::query()->where('share_name' , session()->get('public_user'))->first();

            if (!$aiBot) session()->remove('public_user');

        } else {
            $aiBot = AiBot::adminChat();
        }

        return $aiBot;
    }
}
