<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static Builder connected(int|string|null $userID)
 */
class UserWindowsToken extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id');
    }


    public function whatsappLog()
    {
        return $this->morphMany(WhatsappLog::class, 'gateway', 'desktop', 'whatsapp_id', 'i');
    }

    public function scopeConnected(Builder $builder, $userId = null): Builder
    {
        return $builder->where('user_id' , $userId)
            ->where('status' , WhatsappDevice::STATUS_CONNECTED);
    }
}
