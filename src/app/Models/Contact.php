<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Psy\Util\Str;

/**
 * @property int $user_id
 * @property string $contact_no
 * @property string $name
 * @method static Builder active()
 */
class Contact extends Model
{
    use HasFactory;

    const ACTIVE = 1;
    const INACTIVE = 2;

    protected $fillable = [
        'user_id' ,
        'group_id' ,
        'contact_no' ,
        'name' ,
        'status'
    ];

    public static function replaceWithUnsubscribeLink(string $message , $id): string
    {
        if ($id) {
            $destination = URL::temporarySignedRoute('user.phone.book.group.unsubscribe' , now()->addDays(7) ,
                ['contact' => $id]);

            $hash = \Illuminate\Support\Str::random();
            $url = url('go/'. $hash , null, true);

            ShortUrl::query()->create(['destination' => $destination, 'short_url' => $hash]);
        } else {
            $url = "Contact us to UnSubscribe.";
        }

        return str_replace('{{unsubscribe}}' ,
            $url , $message);
    }


    public function group()
    {
        return $this->belongsTo(Group::class , 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('status' , static::ACTIVE);
    }
}
