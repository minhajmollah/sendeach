<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/**
 * @property ?string $name
 * @property int $user_id
 * @property string $email
 * @method static Builder active()
 */
class EmailContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_group_id',
        'email',
        'name',
        'status'
    ];

    const ACTIVE = 1;
    const INACTIVE = 2;

    public function emailGroup()
    {
    	return $this->belongsTo(EmailGroup::class, 'email_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('status', static::ACTIVE);
    }

    public static function replaceWithUnsubscribeLink(string $message , $id)
    {
        if ($id) {
            $destination = URL::temporarySignedRoute('user.email.group.unsubscribe' , now()->addDays(7) ,
                ['emailContact' => $id]);

            $hash = \Illuminate\Support\Str::random();
            $url = url('go/'. $hash , null, true);

            ShortUrl::query()->create(['destination' => $destination, 'short_url' => $hash]);
        } else {
            $url = "Contact us to UnSubscribe.";
        }

        return str_replace('{{unsubscribe}}' ,
            $url , $message);
    }
}
