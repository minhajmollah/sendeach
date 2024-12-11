<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WhatsappDesktopHealthFailure extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public $user, public $device)
    {
        $this->url = $user instanceof Admin ?  route('admin.desktop.gateway.whatsapp.create') : route('user.desktop.gateway.whatsapp.create');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.whatsapp-desktop-health-failure')->subject('Whatsapp Device Health Failure.');
    }
}
