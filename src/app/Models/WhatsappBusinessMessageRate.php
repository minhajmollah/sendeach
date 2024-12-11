<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappBusinessMessageRate extends Model
{
    use HasFactory;

    protected $fillable = ['credits', 'category', 'type'];
}
