<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditLog extends Model
{
    use HasFactory;


    public static function getDollarPerCredit(): float
    {
        return 0.11;
    }

    public static function getCreditsPerDollar(): float
    {
        return 1 / self::getDollarPerCredit();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
