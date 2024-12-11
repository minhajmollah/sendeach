<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailLog extends Model
{
    use HasFactory, SoftDeletes;

    const PENDING = 1;
    const SCHEDULE = 2;
    const FAILED = 3;
    const SUCCESS = 4;
    const PROCESSING = 5;

    const STATUS_SCOPE = [
        'pending' => self::PENDING,
        'processing' => self::PROCESSING,
        'delivered' => self::SUCCESS,
        'failed' => self::FAILED,
        'schedule' => self::SCHEDULE,
    ];

    protected $fillable = ['user_id', 'from_name', 'reply_to_email', 'sender_id', 'to', 'initiated_time', 'subject', 'message', 'status', 'schedule_status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender()
    {
        return $this->belongsTo(MailConfiguration::class, 'sender_id');
    }


    public function scopeSearch(Builder $builder, $scope = null, $search = '', $gateway = [], $startDate = null, $endDate = null)
    {
        $builder = match ($scope) {
            self::PENDING => $builder->where('status', self::PENDING),
            self::PROCESSING => $builder->where('status', self::PROCESSING),
            self::SUCCESS => $builder->where('status', self::SUCCESS),
            self::SCHEDULE => $builder->where('status', self::SCHEDULE),
            self::FAILED => $builder->where('status', self::FAILED),
            default => $builder
        };

        $builder = $builder->when($search, function ($q) use ($search) {
            return $q->where(fn($q) => $q->where('to', 'like', "%$search%")->orWhereHas('user', function ($user) use ($search) {
                $user->where('email', 'like', "%$search%");
            }));
        })->when($gateway, fn($q) => $q->whereIn('gateway', !is_array($gateway) ? [$gateway] : $gateway));

        if ($startDate) {
            $builder = $builder->whereDate('created_at', Carbon::parse($startDate));
        }
        if ($endDate) {
            $builder = $builder->whereDate('created_at', '>=', Carbon::parse($startDate))
                ->whereDate('created_at', '<=', Carbon::parse($endDate));
        }

        return $builder;
    }

}
