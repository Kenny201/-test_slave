<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllRowsCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $rowsCount,
        public int $userId,
        public string $broadcastQueue = 'allRowsCreated'
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('rows.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'AllRowsCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'rows_count' => $this->rowsCount,
        ];
    }
}
