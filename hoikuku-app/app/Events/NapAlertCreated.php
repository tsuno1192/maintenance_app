<?php

namespace App\Events;

use App\Models\NapAlert;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NapAlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public NapAlert $alert,
    ) {
        $this->alert->loadMissing('child:id,name');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('staff.nap-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'nap.alert.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->alert->id,
            'child_id' => $this->alert->child_id,
            'child_name' => $this->alert->child?->name,
            'message' => $this->alert->message,
            'nap_check_id' => $this->alert->nap_check_id,
            'created_at' => optional($this->alert->created_at)?->toIso8601String(),
        ];
    }
}
