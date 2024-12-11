<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static Builder toProcessAndroid($deviceId)
 */
class SMSlog extends Model
{
    use SoftDeletes;

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

    const STATUS = [
        self::PENDING => 'pending',
        self::PROCESSING => 'processing',
        self::SUCCESS => 'delivered',
        self::FAILED => 'failed',
        self::SCHEDULE => 'schedule',
    ];

    protected $fillable = ['sms_type', 'user_id', 'to', 'batch_id', 'initiated_time', 'message', 'status', 'schedule_status', 'api_gateway_id', 'android_gateway_sim_id', 'android_device_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function smsGateway()
    {
        return $this->belongsTo(SmsGateway::class, 'api_gateway_id');
    }

    public function androidGateway()
    {
        return $this->belongsTo(AndroidApiSimInfo::class, 'android_gateway_sim_id');
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

    public function scopeToProcessAndroid(Builder $builder, $deviceId,): Builder
    {
        return $builder->where('android_device_id', $deviceId)
            ->where('initiated_time' , '<=' , now())
            ->whereIn('status', [self::PENDING, self::SCHEDULE]);
    }

    public function userFCMToken(): BelongsTo
    {
        return $this->belongsTo(UserFcmToken::class, 'android_device_id', 'id');
    }
}
