<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BroadcastResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'recipient_type' => $this->recipient_type,
            'department_id' => $this->department_id,
            'department_name' => $this->department?->name,
            'channel' => $this->channel,
            'status' => $this->status,
            'schedule_at' => $this->schedule_at?->toISOString(),
            'sent_by' => $this->sentBy?->name,
            'sent_by_id' => $this->sent_by,
            'recipient_count' => $this->recipient_count,
            'delivered_count' => $this->delivered_count,
            'failed_count' => $this->failed_count,
            'created_at' => $this->created_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
}
