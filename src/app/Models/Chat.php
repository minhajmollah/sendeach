<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $message
 * @property integer $conversation_id
 * @property string $status
 * @property string $type
 * @property bool $is_sender
 * @property array $data
 * @property ChatConversation $conversation
 */
class Chat extends Model
{
    use HasFactory;

    const MESSAGEABLE_TYPE_GUEST = 'guest';

    const STATUS_DELIVERED = 'delivered';

    protected $fillable = ['message' , 'conversation_id' , 'status' , 'type' , 'is_sender' , 'data' , 'messenger_message_id'];

    const TYPE_TEXT = 'text';
    const STATUS_UNREAD = 'unread';
    const STATUS_READ = 'read';

    const STATUS = [self::STATUS_UNREAD , self::STATUS_READ];

    protected $casts = ['data' => AsArrayObject::class];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class , 'conversation_id');
    }

    public function scopeSelectForAjax(Builder $builder): Builder
    {
        return $builder->select('id' , 'created_at' , 'message' , 'type' , 'status' , 'is_sender' , 'conversation_id');
    }
}
