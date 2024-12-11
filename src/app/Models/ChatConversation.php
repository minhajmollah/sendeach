<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @property FacebookMessengerSender|User|null $messageable
 * @property ?AiBot $aiBot
 * @property array $data
 */
class ChatConversation extends Model
{

    protected $fillable = ['ai_bot_id', 'messageable_type', 'messageable_id', 'data'];

    protected $casts = ['data' => AsArrayObject::class];


    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'conversation_id')
            ->orderBy('created_at', 'DESC')
            ->orderBy('is_sender');
    }

    public function aiBot()
    {
        return $this->belongsTo(AiBot::class, 'ai_bot_id');
    }

    public function users(): MorphTo
    {
        return $this->morphTo(User::class, 'messagable');
    }

    public function messageable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function mapParticipantable($participantable)
    {
        return [
            'messageable_id' => is_object($participantable) ? $participantable->id : $participantable,
            'messageable_type' => is_object($participantable) ?
                get_class($participantable) : Chat::MESSAGEABLE_TYPE_GUEST
        ];
    }

    public function createMessage($message, $status = Chat::STATUS_UNREAD, $attr = []): Chat|null
    {
        if (!$message) return null;

        return Chat::query()->create(array_merge([
            'message' => $message,
            'conversation_id' => $this->id,
            'type' => Chat::TYPE_TEXT,
            'status' => $status,
            'is_sender' => true,
            'created_at' => now()->subSecond()
        ], $attr));
    }

    public function chatWithAi(string $message, $status = Chat::STATUS_UNREAD, $attr = []): ?Chat
    {
        $this->createMessage($message, Chat::STATUS_READ, $attr);

        // Pause Chat Conversation if user had connect to human functionality for this conversation
        if (($pausedAt = Arr::get($this->data, 'paused_at')) && ($pauseDuration = Arr::get($this->data, 'pause_duration'))) {
            if (Carbon::parse($pausedAt)->diffInMinutes(now()) < $pauseDuration) {
                return null;
            }
        }

        $customReply = null;

        try {
            $customReply = BotCustomReplies::search($message, $this->aiBot)
                ->first();

            if ($customReply->to_pause && $customReply->pause_duration > 0) {
                $this->data = $this->data ?: [];
                Arr::set($this->data, 'paused_at', now());
                Arr::set($this->data, 'pause_duration', $customReply->pause_duration);
                $this->save();
            }
        } catch (\Exception $exception) {
            logger()->error(json_encode($exception));
        }

        $reply = null;


        if ($customReply) {
            $reply = $customReply->reply;
        } else {

            $key = 'ai-web-chat-bot:' . auth()->id() . $this->aiBot->id;

            if (RateLimiter::tooManyAttempts($key, $this->aiBot->messages_per_minute)) {
//                $seconds = RateLimiter::availableIn($key);
//                return new Chat(['message' => "Maximum Message Received per Minute. Please try again in $seconds seconds."]);
                return null;
            }

            RateLimiter::hit($key);

            $reply = $this->aiBot?->chat($message, $this->id);
        }

        if ($reply) {
            return $this->createResponse($reply, $status, $attr);
        }

        return null;
    }

    public function createResponse($message, $status = Chat::STATUS_UNREAD, $attr = []): Chat|null
    {
        if (!$message) return null;

        return Chat::query()->create(array_merge([
            'message' => $message,
            'conversation_id' => $this->id,
            'type' => Chat::TYPE_TEXT,
            'status' => $status,
            'is_sender' => false,
        ], $attr));
    }

    public function unreadChatsForUser(): HasMany
    {
        return $this->chats()->where('status', Chat::STATUS_UNREAD)
            ->where('is_sender', '=', false);
    }

    public static function startConversationByparticipants($participantable, AiBot $bot): ChatConversation
    {
        return static::query()->firstOrCreate(
            array_merge(static::mapParticipantable($participantable), ['ai_bot_id' => $bot?->id])
        );
    }

    public function readUnReadMessages(): Collection
    {
        $unreadMessages = $this->unreadChatsForUser()->selectForAjax()->get();

        $this->unreadChatsForUser()->update(['status' => Chat::STATUS_READ]);

        return $unreadMessages;
    }

    public function createGreeting(string $greetingText, $status = Chat::STATUS_READ): ?Chat
    {
        return $this->createResponse($greetingText, $status,
            attr: ['messenger_message_id' => $message['id']['id'] ?? '']);
    }
}
