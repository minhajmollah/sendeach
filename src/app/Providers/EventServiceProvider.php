<?php

namespace App\Providers;

use App\Events\MessengerWebhookReceived;
use App\Events\NewAiChatResponse;
use App\Events\NewWhatsappWebMessage;
use App\Listeners\AddAiResponseToCustomReply;
use App\Listeners\HandleMessengerWebhook;
use App\Listeners\HandleWhatsappWebMessage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class ,
        ] ,
        MessengerWebhookReceived::class => [
            HandleMessengerWebhook::class
        ] ,
        NewWhatsappWebMessage::class => [
            HandleWhatsappWebMessage::class
        ] ,
        NewAiChatResponse::class => [
            AddAiResponseToCustomReply::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
