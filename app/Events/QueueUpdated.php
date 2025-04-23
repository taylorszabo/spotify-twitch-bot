<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public array $uris;

    public function __construct(array $uris)
    {
        $this->uris = $uris;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('spotify');
    }

    public function broadcastAs(): string
    {
        return 'QueueUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'queue' => $this->uris
        ];
    }
}


