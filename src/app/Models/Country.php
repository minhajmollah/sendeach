<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iso' ,
        'name' ,
        'iso3' ,
        'num_code' ,
        'phone_code',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'num_code' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Scope a query to only include active countries.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }
}
