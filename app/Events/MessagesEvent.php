<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessagesEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messenger;

    public function __construct($content,$isRole,$name,$time,$user_id,$isLogout = false)
    {
        $this->messenger["content"] = $content;
        $this->messenger["isRole"] = $isRole;
        $this->messenger["time"] = $time;
        $this->messenger["name"] = $name;
        $this->messenger["idUser"] = $user_id;
        $this->messenger["isLogout"] = $isLogout;
    }

    public function broadcastOn()
    {
        return ['messenger-channel'];
    }

    public function broadcastAs()
    {
        return 'messenger-event';
    }
}
