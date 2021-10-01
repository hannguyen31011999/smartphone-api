<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderMessages implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order = [];

    public function __construct($code,$time,$message,$name)
    {
        $this->order["code"] = $code;
        $this->order["time"] = $time;
        $this->order["messages"] = $message;
        $this->order["name"] = $name;
    }

    public function broadcastOn()
    {
        return ['order-channel'];
    }

    public function broadcastAs()
    {
        return 'order-event';
    }
}
