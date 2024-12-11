<?php

namespace App\Events;

use App\Models\WhatsappDevice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewWhatsappWebMessage
{
    use Dispatchable , InteractsWithSockets , SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public WhatsappDevice $whatsappDevice , public array $message)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
