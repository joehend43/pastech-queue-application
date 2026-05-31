<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueCalled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue;
    public $kasirName;
    public $status;

    public function __construct(Queue $queue, $kasirName, $status = 'called')
    {
        $this->queue = $queue;
        $this->kasirName = $kasirName;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        // Menentukan nama channel tempat Display mendengarkan sinyal
        return [
            new Channel('queue-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        // Nama samaran event saat diterima di JavaScript nanti
        return 'queue.called';
    }
}