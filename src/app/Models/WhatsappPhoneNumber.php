<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappPhoneNumber extends Model
{
    use HasFactory;

    protected $guarded = ['created_at', 'updated_at'];

    public function whatsapp_account(): BelongsTo
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_business_id', 'whatsapp_business_id');
    }

    public static function get_admin_phone()
    {
        return WhatsappPhoneNumber::where('whatsapp_phone_number_id' , config('whatsapp.admin_phone_id'))->first();
    }

    public function isActive()
    {
        if($this->type == WhatsappAccessToken::TYPE_EMBEDDED_FORM && (!$this->is_registered || $this->status != 'VERIFIED'))
        {
            return false;
        }

        return true;
    }
}
