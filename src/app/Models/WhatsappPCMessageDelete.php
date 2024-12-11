<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappPCMessageDelete extends Model
{
    use HasFactory;

    protected $fillable = ['user_id' , 'keywords', 'message', 'status'];

    protected $table = 'whatsapp_pc_message_deletes';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
