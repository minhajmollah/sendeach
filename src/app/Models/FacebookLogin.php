<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookLogin extends Model
{
    use HasFactory;

    protected $fillable = ['userID' , 'accessToken' ,
        'expiresIn' , 'user_id' , 'expiresIn' , 'signedRequest' , 'graphDomain' , 'data_access_expiration_time' , 'whatsapp_access_token_id'];
}
