<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $whatsapp_business_id
 */
class WhatsappAccount extends Model
{
    use HasFactory;

    protected $guarded = ['created_at', 'updated_at'];

    public function whatsapp_phone_numbers(): HasMany
    {
        return $this->hasMany(WhatsappPhoneNumber::class , 'whatsapp_business_id' , 'whatsapp_business_id');
    }

    public function whatsapp_templates(): HasMany
    {
        return $this->hasMany(WhatsappTemplate::class , 'whatsapp_business_id' , 'whatsapp_business_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id');
    }

    public function whatsappAccessToken()
    {
        return $this->belongsTo(WhatsappAccessToken::class , 'whatsapp_access_token_id' , 'id');
    }
}
