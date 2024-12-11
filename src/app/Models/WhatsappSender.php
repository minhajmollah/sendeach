<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class WhatsappSender extends Model
{
    use HasFactory;

    protected $fillable = ['phone', 'name'];


    public function chatConversation(): MorphOne
    {
        return $this->morphOne(ChatConversation::class, 'messageable');
    }
}
