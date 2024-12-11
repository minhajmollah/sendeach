<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static Builder byUserType(int|string|null $id)
 * @method static Builder connected(int|string|null $userId)
 * @property WhatsappBot|null $whatsappBot
 */
class WhatsappDevice extends Model
{
    use HasFactory;

    protected $table = 'wa_device';
    protected $guarded = [];
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_INITIATE = 'initiate';
    const STATUS_CONNECTED = 'connected';

    protected $fillable = ['name' , 'number' , 'delay_time' , 'user_id' , 'session_id' , 'qr' , 'status' , 'user_type' , 'data'];

    protected $casts = ['data' => AsArrayObject::class];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id');
    }

    public function scopeConnected(Builder $builder , $userId = null): Builder
    {
        return $builder->where('user_id' , $userId)
            ->where('status' , WhatsappDevice::STATUS_CONNECTED);
    }

    public function scopeByUserType(Builder $builder , $userId = null): Builder
    {
        if ($userId) {
            return $builder->where('user_id' , $userId)->where('user_type' , 'user');
        }

        return $builder;
    }

    public function whatsappBot(): HasOne
    {
        return $this->hasOne(WhatsappBot::class, 'whatsapp_gateway_id', 'id');
    }
}
