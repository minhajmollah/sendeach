<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PublicOTP extends Mailable
{
    use Queueable, SerializesModels;

    public $domain;
    public $otp;
    public $watermark;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp, $domain, public $username = null)
    {
        $this->otp = $otp;
        $this->domain = $domain;
        $this->watermark = GeneralSetting::admin()->free_watermark;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.public-o-t-p')
            ->subject($this->domain.' - Verification OTP | SendEach.com');
    }
}
