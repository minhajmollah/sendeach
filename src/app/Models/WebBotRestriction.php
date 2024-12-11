<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebBotRestriction extends Model
{
    use HasFactory;
    protected $fillable = ['domain_name', 'status', 'user_id'];

    public function scopeGetForUser($query, $domain_name)
    {
        return $query->where('domain_name', $domain_name);
    }
}