<?php

namespace App\Events;

use App\Models\DocumentVersion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentVersionCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public DocumentVersion $version)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('projects.'.$this->version->research_project_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.version.created';
    }

    public function broadcastWith(): array
    {
        return [
            'version_id' => $this->version->id,
            'project_id' => $this->version->research_project_id,
            'version_number' => $this->version->version_number,
            'author_id' => $this->version->author_id,
        ];
    }
}
