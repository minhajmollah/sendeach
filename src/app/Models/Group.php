<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $id
 * @property Collection $contacts
 * @property string $type
 */
class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id' ,
        'name' ,
        'status',
        'type'
    ];

    const TYPE_SYSTEM = 'system';
    const TYPE_USER = 'user';

    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public static function unsubscribedContacts($userId): Builder
    {
        return Contact::query()->where('user_id' , $userId)
            ->where('group_id' , self::systemUnsubscribedGroup($userId)->id);
    }

    public static function systemUnsubscribedGroup($userId): Group
    {
        /** @var Group $group */
        $group = Group::query()->firstOrCreate([
            'user_id' => $userId ,
            'name' => 'UnSubscribed Users',
            'type' => self::TYPE_SYSTEM
        ]);

        return $group;
    }

    public function isDeletable()
    {
        return $this->type != self::TYPE_SYSTEM;
    }
}
