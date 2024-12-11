<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string $psid
 * @property integer $facebook_messenger_id
 * @property array $data
 */
class FacebookMessengerSender extends Model
{
    use HasFactory;

    protected $fillable = ['psid', 'data', 'facebook_messenger_id'];

    protected $casts = ['data' => AsArrayObject::class];

    public function facebookMessenger(): BelongsTo
    {
        return $this->belongsTo(FacebookMessenger::class);
    }

    public function chatConversation(): MorphOne
    {
        return $this->morphOne(ChatConversation::class, 'messageable');
    }
}
