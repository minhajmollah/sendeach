<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailGroup extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id' ,
        'name' ,
        'status',
        'type'
    ];

    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmailContact::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public static function unsubscribedContacts($userId): Builder
    {
        return EmailContact::query()->where('user_id' , $userId)
            ->where('email_group_id' , self::systemUnsubscribedGroup()->id);
    }

    public static function systemUnsubscribedGroup($userId = null): EmailGroup
    {
        /** @var EmailGroup $group */
        $group = EmailGroup::query()->firstOrCreate([
            'user_id' => $userId ,
            'name' => 'UnSubscribed Users',
            'type' => Group::TYPE_SYSTEM
        ]);

        return $group;
    }

    public function isDeletable()
    {
        return $this->type != Group::TYPE_SYSTEM;
    }
}
