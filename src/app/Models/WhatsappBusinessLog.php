<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $whatsapp_phone_number_id
 * @property int|null $user_id
 * @property string|null $whatsapp_business_id
 * @property string $whatsapp_template_id
 * @property string $message
 * @property int $status
 * @property string $to
 */
class WhatsappBusinessLog extends Model
{
    use HasFactory;

    const PENDING = 1;
    const SCHEDULE = 2;
    const FAILED = 3;
    const SUCCESS = 4;
    const PROCESSING = 5;


    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function whatsapp_phone_number()
    {
        return $this->belongsTo(WhatsappPhoneNumber::class , 'whatsapp_phone_number_id' , 'whatsapp_phone_number_id');
    }

    public static function startLog($to , WhatsappTemplate $template , $headerParameters , $bodyParameters ,
                                    WhatsappPhoneNumber $phoneNumber , $userId = null ,
                                    WhatsappAccount $whatsappAccount = null): WhatsappBusinessLog
    {
        $whatsappBusinessLog = new WhatsappBusinessLog();
        $whatsappBusinessLog->whatsapp_phone_number_id = $phoneNumber->whatsapp_phone_number_id;
        $whatsappBusinessLog->user_id = $userId;
        $whatsappBusinessLog->whatsapp_business_id = $whatsappAccount->whatsapp_business_id;
        $whatsappBusinessLog->whatsapp_template_id = $template->whatsapp_template_id;
        $whatsappBusinessLog->to = $to;
        $whatsappBusinessLog->message = $template->toMessageText($headerParameters , $bodyParameters);

        $whatsappBusinessLog->status = WhatsappBusinessLog::PENDING;

        $whatsappBusinessLog->save();

        return $whatsappBusinessLog;
    }
}
