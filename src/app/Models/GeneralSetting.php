<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class GeneralSetting extends Model
{
    use HasFactory;


    protected $casts = [
        'frontend_section' => 'object',
        's_login_google_info' => 'object',
        'data' => AsArrayObject::class
    ];

    public static function getFreeWatermark()
    {
        return self::admin()->free_watermark;
    }

    /**
     * @return mixed
     */
    public static function admin(): GeneralSetting
    {
        return GeneralSetting::where('user_type', 'admin')->first();
    }

    public function getWhatsappGatewayStatus(): array
    {
        $gatewayStatus[WhatsappLog::GATEWAY_WEB] = Arr::get($this->data, 'whatsapp.gateway.' . WhatsappLog::GATEWAY_WEB . '.status');
        $gatewayStatus[WhatsappLog::GATEWAY_DESKTOP] = Arr::get($this->data, 'whatsapp.gateway.' . WhatsappLog::GATEWAY_DESKTOP . '.status');

        return $gatewayStatus;
    }
}
