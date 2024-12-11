<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @method static Builder byUser(int|string|null $id)
 * @property string $message
 * @property ?array $choices
 * @property AiBot $aiBot
 * @property int $bot_id
 */
class AiBotResponse extends Model
{
    use HasFactory;

    protected $fillable = ['bot_id' , 'total_tokens_used' , 'temperature' , 'choices' ,
        'message' , 'feedback' , 'likes' , 'dis_likes' , 'data' , 'chat_conversation_id', 'model', 'reply'];

    protected $casts = [
        'choices' => AsArrayObject::class ,
        'data' => AsArrayObject::class
    ];

    public function scopeByUser(Builder $builder , $userId = null): Builder
    {
        return AiBotResponse::query()
            ->whereIn('bot_id' , AiBot::query()->select('id')
                ->where('user_id' , $userId));
    }

    public static function asChats($responses , $data = []): Collection|\Illuminate\Database\Eloquent\Collection
    {
        return $responses->map(function ($response) use ($data) {
            return array_merge([
                'message' => $response->message ,
                'reply' => $response->choices  ? Arr::get(Arr::first($response->choices , function ($choice) {
                    return Arr::get($choice , 'message.role') == 'assistant';
                }) , 'message.content') : '',
                'ai_bot_response_id' => $response->id ,
                'ai_bot_id' => $response->bot_id ,
            ] , $data);
        });
    }

    public function aiBot(): BelongsTo
    {
        return $this->belongsTo(AiBot::class , 'bot_id' , 'id');
    }
}
