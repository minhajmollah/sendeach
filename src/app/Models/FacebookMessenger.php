<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property string $page_access_token
 * @property string $page_id
 * @property array $data
 * @property string $challenge
 * @property integer $user_id
 * @property int|null $id
 * @property integer $facebook_login_id
 * @property ?string $greetings_text
 */
class FacebookMessenger extends Model
{
    use HasFactory;

    protected $fillable = ['user_id' , 'page_access_token' , 'page_id' , 'data' ,
        'challenge' , 'facebook_login_id', 'ai_bot_id', 'greetings_text'];

    const STATUS_ACTIVE = '1';
    const STATUS_IN_ACTIVE = '0';

    protected $casts = ['data' => AsArrayObject::class];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function senders(): HasMany
    {
        return $this->hasMany(FacebookMessengerSender::class , 'facebook_messenger_id' , 'id');
    }

    public function chatConversations(): HasManyThrough
    {
        return $this->hasManyThrough(ChatConversation::class , FacebookMessengerSender::class ,
            'facebook_messenger_id' , 'messageable_id')
            ->where('chat_conversations.messageable_type' , FacebookMessengerSender::class);
    }

    public function facebookLogin(): BelongsTo
    {
        return $this->belongsTo(FacebookLogin::class);
    }

    public function aiBot(): BelongsTo
    {
        return $this->belongsTo(AiBot::class, 'ai_bot_id', 'id');
    }

    public function getGreetingsText(): ?string
    {
        if ($greetingText = $this->greetings_text) {
            $watermark = config('requirements.greetings_text_postfix');
            return $greetingText . "\n\n*$watermark*";
        }

        return null;
    }
}
