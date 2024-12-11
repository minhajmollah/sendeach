<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Passport\HasApiTokens;
use Laravel\Sanctum\HasApiTokens;



class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'status',
        'phone',
        'password',
        'google_id',
        'otp',
        'otp_time',
        'last_logged_in',
        'data'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'otp_time' => 'datetime',
        'last_logged_in' => 'datetime',
        'data' => AsArrayObject::class
    ];

    const USER_TYPE_ADMIN = 'admin';
    const USER_TYPE_USER = 'user';

    const ABILITY_WEB_BOT = 'web-bot';
    const ABILITY_SEND_WHATSAPP = 'send-whatsapp';
    const ABILITY_SEND_SMS = 'sms-whatsapp';

    const ABILITIES = [
        self::ABILITY_WEB_BOT => 'WebChat Bot',
        self::ABILITY_SEND_WHATSAPP  => 'Send Whatsapp Messages',
        self::ABILITY_SEND_SMS => 'Send SMS',
    ];


    public function scopeUnverified($query)
    {
        return $query->where('status', 3);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', 2);
    }

    public function create_default_groups()
    {
        Group::updateOrCreate(['name' => "Default"], [
            'name' => "Default",
            'status' => 1,
        ]);

        EmailGroup::updateOrCreate(['name' => "Default"], [
            'name' => "Default",
            'status' => 1,
        ]);
    }

    public function add_to_admin_groups()
    {
        $user = $this;

        if ($user->phone) {

            $admin_sms_group = Group::whereNull('user_id')->where('name', 'All Users')->first();
            if (!$admin_sms_group) {
                $admin_sms_group = Group::create([
                    'name' => "All Users",
                    'status' => 1,
                ]);
            }

            Contact::updateOrCreate([
                'group_id' => $admin_sms_group->id,
                'contact_no' => $user->phone,
            ], [
                'group_id' => $admin_sms_group->id,
                'contact_no' => $user->phone,
                'name' => $user->name,
                'status' => 1,
            ]);
        }

        if ($user->email) {
            $admin_email_group = EmailGroup::whereNull('user_id')->where('name', 'All Users')->first();
            if (!$admin_email_group) {
                $admin_email_group = EmailGroup::create([
                    'name' => "All Users",
                    'status' => 1,
                ]);
            }

            EmailContact::updateOrCreate([
                'email_group_id' => $admin_email_group->id,
                'email' => $user->email,
            ], [
                'email_group_id' => $admin_email_group->id,
                'email' => $user->email,
                'name' => $user->name,
                'status' => 1,
            ]);
        }
    }

    public function ticket()
    {
        return $this->hasMany(SupportTicket::class, 'user_id');
    }

    public function group()
    {
        return $this->hasMany(Group::class, 'user_id');
    }

    public function emailGroup()
    {
        return $this->hasMany(EmailGroup::class, 'user_id');
    }

    public function contact()
    {
        return $this->hasMany(Contact::class, 'user_id');
    }

    public function emailContact()
    {
        return $this->hasMany(EmailContact::class, 'user_id');
    }


    public function template()
    {
        return $this->hasMany(Template::class, 'user_id')->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_id')->latest();
    }

    public function ableToSendWithoutWatermark()
    {
        return $this->credit > 1;
    }

    public function defaultWhatsappGateway()
    {
        return $this->default_whatsapp_gateway;
    }

    public function getAvailableDepositInDollars(): float
    {
        return CreditLog::getDollarPerCredit() * $this->credit;
    }
}
