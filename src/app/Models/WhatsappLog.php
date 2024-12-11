<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappLog extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_SCOPE = [
        'pending' => WhatsappLog::PENDING ,
        'processing' => WhatsappLog::PROCESSING ,
        'delivered' => WhatsappLog::SUCCESS ,
        'failed' => WhatsappLog::FAILED ,
        'schedule' => WhatsappLog::SCHEDULE ,
        'pause' => WhatsappLog::PAUSE
    ];

    protected $fillable = ['user_id' , 'to' , 'gateway' , 'initiated_time' ,
        'message' , 'status' , 'document' , 'audio' , 'video' , 'image' ,
        'schedule_status' , 'auto_delete' , 'whatsapp_id' , 'file_caption' , 'data', 'batch_id'];

    protected $casts = ['initiated_time' => 'datetime', 'data' => AsArrayObject::class];

    const PENDING = 1;
    const SCHEDULE = 2;
    const FAILED = 3;
    const SUCCESS = 4;
    const PROCESSING = 5;
    const PAUSE = 6;

    const SCHEDULE_STATUS_YES = 2;
    const SCHEDULE_STATUS_NO = 1;

    const GATEWAY_BUSINESS = 'business';
    const GATEWAY_WEB = 'web';
    const GATEWAY_DESKTOP = 'desktop';
    const GATEWAY_BUSINESS_OWN = 'own-business';

    const GATEWAYS = [self::GATEWAY_WEB , self::GATEWAY_BUSINESS , self::GATEWAY_DESKTOP , self::GATEWAY_BUSINESS_OWN];
    const ADMIN_GATEWAYS = [self::GATEWAY_WEB , self::GATEWAY_BUSINESS, self::GATEWAY_DESKTOP];

    const GATEWAY = [
        self::GATEWAY_BUSINESS => 'SendEach WhatsApp API Gateway' ,
        self::GATEWAY_DESKTOP => 'PC Gateway' ,
        self::GATEWAY_WEB => 'Web Gateway' ,
        self::GATEWAY_BUSINESS_OWN => 'Own WhatsApp API Gateway' ,
    ];

    const STATUSES = [self::PENDING , self::PROCESSING , self::FAILED , self::SCHEDULE , self::SUCCESS, self::PAUSE];

    const GATEWAY_STATUS_ONLINE = 1;
    const GATEWAY_STATUS_OFFLINE = 0;
    const GATEWAY_STATUSES = [self::GATEWAY_STATUS_OFFLINE, self::GATEWAY_STATUS_ONLINE];
    const GATEWAY_STATUS = [
        self::GATEWAY_STATUS_ONLINE => 'Online',
        self::GATEWAY_STATUS_OFFLINE  => 'Offline'
    ];

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function whatsappGateway()
    {
        return $this->belongsTo(WhatsappDevice::class , 'whatsapp_id');
    }

    public function whatsappPCGateway()
    {
        return $this->belongsTo(UserWindowsToken::class , 'whatsapp_id');
    }

    public function whatsapp_phone_number()
    {
        return $this->belongsTo(WhatsappPhoneNumber::class , 'whatsapp_phone_number_id' , 'whatsapp_phone_number_id');
    }

    public static function startLog($whatsappGateway , $user , $to , $message , $gateway)
    {

        $log = new WhatsappLog();
        $log->whatsapp_id = $whatsappGateway?->id;
        $log->user_id = is_object($user) ? $user?->id : $user;
        $log->to = $to;
        $log->initiated_time = Carbon::now();
        $log->message = $message;
        $log->status = 1;
        $log->gateway = $gateway;

        $log->save();

        return $log;
    }

    public static function startBusinessLog($to , WhatsappTemplate $template , $headerParameters , $bodyParameters ,
                                            WhatsappPhoneNumber $phoneNumber , $userId ,
                                            WhatsappAccount $whatsappAccount): WhatsappLog
    {
        $WhatsappLog = new WhatsappLog();
        $WhatsappLog->whatsapp_phone_number_id = $phoneNumber->whatsapp_phone_number_id;
        $WhatsappLog->user_id = $userId;
        $WhatsappLog->whatsapp_business_id = $whatsappAccount->whatsapp_business_id;
        $WhatsappLog->whatsapp_template_id = $template->whatsapp_template_id;
        $WhatsappLog->to = $to;
        $WhatsappLog->message = $template->toMessageText($headerParameters , $bodyParameters);

        $WhatsappLog->status = WhatsappLog::PENDING;
        $WhatsappLog->gateway = WhatsappLog::GATEWAY_BUSINESS;

        $WhatsappLog->save();

        return $WhatsappLog;
    }

    public function scopeByDesktop(Builder $builder , $userId = null , $windowsToken = null , $status = WhatsappLog::PENDING)
    {
        return $builder
            ->when($userId , fn($q) => $q->where('user_id' , $userId))
            ->when($windowsToken , fn($q) => $q->where(
                fn($q) => $q->where('whatsapp_id' , $windowsToken->id)->orWhere('whatsapp_id' , null))
            )
            ->when($status , fn($q) => $q->where('status' , $status))
            ->where('gateway' , WhatsappLog::GATEWAY_DESKTOP)
            ->orderBy('id' , 'ASC');
    }

    public function scopeSearch(Builder $builder , $scope = null , $search = '' , $gateway = [WhatsappLog::GATEWAY_DESKTOP ,
        WhatsappLog::GATEWAY_WEB] ,     $startDate = null , $endDate = null)
    {
        $builder = match ($scope) {
            WhatsappLog::PENDING => $builder->where('status' , WhatsappLog::PENDING) ,
            WhatsappLog::PROCESSING => $builder->where('status' , WhatsappLog::PROCESSING) ,
            WhatsappLog::SUCCESS => $builder->where('status' , WhatsappLog::SUCCESS) ,
            WhatsappLog::PAUSE => $builder->where('status' , WhatsappLog::PAUSE) ,
            WhatsappLog::SCHEDULE => $builder->where('status' , WhatsappLog::SCHEDULE) ,
            WhatsappLog::FAILED => $builder->where('status' , WhatsappLog::FAILED) ,
            default => $builder
        };

        $builder = $builder->when($search , function ($q) use ($search) {
            return $q->where(fn($q) => $q->where('to' , 'like' , "%$search%")->orWhereHas('user' , function ($user) use ($search) {
                $user->where('email' , 'like' , "%$search%");
            }));
        })->when($gateway , fn($q) => $q->whereIn('gateway' , !is_array($gateway) ? [$gateway] : $gateway));

        if ($startDate) {
            $builder = $builder->whereDate('created_at' , Carbon::parse($startDate));
        }
        if ($endDate) {
            $builder = $builder->whereDate('created_at' , '>=' , Carbon::parse($startDate))
                ->whereDate('created_at' , '<=' , Carbon::parse($endDate));
        }

        return $builder;
    }

    public function getGatewayName()
    {
        return $this->gateway == \App\Models\WhatsappLog::GATEWAY_DESKTOP ?
            $this->whatsappPCGateway?->device_id
            : $this->whatsappGateway?->name;
    }

    public function ScopeToProcess(Builder $builder): Builder
    {
        return $builder->where('initiated_time' , '<=' , now())->whereIn('status', [self::PENDING, self::SCHEDULE]);
    }

    public static function delayInitiatedTime($key, Carbon &$initiatedTime): Carbon
    {
        $sentCache = \cache()->get($key) ?: [];
        $sentCache['count'] = $sentCache['count'] ?? 0;
        $sentCache['delayed_count'] = $sentCache['delayed_count'] ?? 0;
        $sentCache['last_sent_at'] = $sentCache['last_sent_at'] ?? now();

        $sent = $sentCache['count'] - $sentCache['delayed_count'];

        // Add delay of 5-10 Mins for each 100 messages if messages are continuously sent within 15 minutes.
        if ($sentCache && ($sent > 100) && ($sentCache['last_sent_at']->diffInMinutes(now()) < 15)) {
            $initiatedTime->addMinutes(rand(5, 15));
            $sentCache['delayed_count'] = $sentCache['count'];
        }

        $sentCache['count'] = $sentCache['count'] + 1;
        $sentCache['last_sent_at'] = now();
        cache()->put($key , $sentCache , CarbonInterval::hours(24));

        return $initiatedTime;
    }

    public static function canSend($key, $count = 1): bool
    {
        $canSend = true;

        $sentCache = \cache()->get($key) ?: [];
        $sentCache['count'] = $sentCache['count'] ?? 0;
        $sentCache['total_count'] = $sentCache['total_count'] ?? 0;
        $sentCache['last_sent_at'] = $sentCache['last_sent_at'] ?? null;

//        info('Current Pull: '.$count.' Total Sent: '.$sentCache['count']. ' Key: '.$key. ' Last Sent At: '
//            .$sentCache['last_sent_at']?->toDateString(). ' Pending Minutes '.$sentCache['last_sent_at']?->diffInMinutes(now()));

        // Add delay of 5-10 Minutes for each 100 messages if messages are continuously sent within 15 minutes.
        if (($sentCache['count'] >= 10) && ($sentCache['last_sent_at'] && $sentCache['last_sent_at']->diffInMinutes(now()) < 15)) {
//            $sentCache['last_sent_at'] = $sentCache['last_sent_at']->toDateTimeString();
//            info($sentCache);
            return false;
        }

        if($sentCache['count'] >= 10)
        {
            $sentCache['count'] = 0;
        }

        // Add delay of 50 Minutes for each 1000 messages if messages are continuously sent within 15 minutes.
        if (($sentCache['total_count'] >= 1000) && ($sentCache['last_sent_at'] && $sentCache['last_sent_at']->diffInMinutes(now()) < 60)) {
//            $sentCache['last_sent_at'] = $sentCache['last_sent_at']->toDateTimeString();
//            info($sentCache);
            return false;
        }

        if($sentCache['total_count'] >= 1000)
        {
            $sentCache['total_count'] = 0;
        }

        $sentCache['count'] = $sentCache['count'] + $count;
        $sentCache['total_count'] = $sentCache['total_count'] + $count;
        $sentCache['last_sent_at'] = now();

        cache()->put($key , $sentCache , CarbonInterval::hours(24));

        return true;
    }
}
