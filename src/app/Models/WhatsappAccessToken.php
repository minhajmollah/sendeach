<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $access_token
 */
class WhatsappAccessToken extends Model
{
    use HasFactory;

    const TYPE_EMBEDDED_FORM = 'EMBEDDED_FORM';
    const TYPE_OWN = 'OWN';

    protected $guarded = ['created_at', 'updated_at'];

    public function whatsappBusinessAccounts(): HasMany
    {
        return $this->hasMany(WhatsappAccount::class);
    }

    public function facebookLogin(): HasOne
    {
        return $this->hasOne(FacebookLogin::class);
    }

    public static function getAdminAccessToken()
    {
        return static::query()->where('user_id', null)
            ->where(function ($query){
                return $query->whereNull('type')->orWhere('type', WhatsappAccessToken::TYPE_OWN);
            });
    }
}
