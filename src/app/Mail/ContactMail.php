<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable , SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public $message , public $subject , public $name , public $email ,)
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.contact-mail')->subject($this->subject)->replyTo($this->email)->from('support@sendeach.com');
    }
}
