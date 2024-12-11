<?php

namespace App\Providers;

use App\Models\MailConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $config = cache()->remember('admin-smtp-mail-configuration', 86400, function () {
            $mail = MailConfiguration::query()
                ->where('name', MailConfiguration::TYPE_SMTP)
                ->where('user_type', User::USER_TYPE_ADMIN)
                ->where('status', 1)->first();

            if ($mail && $mail->driver_information) {

                return [
                    'driver' => $mail->driver_information->driver,
                    'mailers' => [
                        'smtp' => [
                            'host' => $mail->driver_information->host ?? "smtp",
                            'port' => $mail->driver_information->smtp_port,
                            'encryption' => $mail->driver_information->encryption ?? "ssl",
                            'username' => $mail->driver_information->username,
                            'password' => $mail->driver_information->password,
                        ],
                    ],
                    'pretend' => false,
                    'from' => [
                        'address' => $mail->driver_information->from->address,
                        'name' => $mail->driver_information->from->name
                    ],
                ];
            }

            return null;
        });


        if ($config) {
            Config::set('mail.driver', $config['driver']);
            Config::set('mail.mailers.smtp', $config['mailers']['smtp']);
            Config::set('mail.from', $config['from']);
            Config::set('mail.host', $config['mailers']['smtp']['host']);
            Config::set('mail.port', $config['mailers']['smtp']['port']);
            Config::set('mail.encryption', $config['mailers']['smtp']['encryption']);
            Config::set('mail.username', $config['mailers']['smtp']['username']);
            Config::set('mail.password', $config['mailers']['smtp']['password']);
        }
    }
}
