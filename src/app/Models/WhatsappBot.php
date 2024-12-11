<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property ?AiBot $aiBot
 * @property int $user_id
 */
class WhatsappBot extends Model
{
    protected $fillable = ['whatsapp_gateway_id' , 'ai_bot_id' , 'user_id' , 'data' , 'is_enabled' , 'handle_only_unknown_user'];

    protected $casts = ['data' => AsArrayObject::class];

    public function chatConversation(): MorphOne
    {
        return $this->morphOne(ChatConversation::class, 'messageable');
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(WhatsappDevice::class , 'whatsapp_gateway_id' , 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiBot(): BelongsTo
    {
        return $this->belongsTo(AiBot::class , 'ai_bot_id' , 'id');
    }

    public function getGreetingsText(): ?string
    {
        if ($greetingText = $this->data['greetings_text']) {
            $watermark = config('requirements.greetings_text_postfix');
            return $greetingText . "\n\n*$watermark*";
        }

        return null;
    }
}
