<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowUpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visitor_id' => $this->visitor_id,
            'visitor_name' => $this->visitor?->name,
            'visitor_phone' => $this->visitor?->phone,
            'contact_method' => $this->contact_method,
            'notes' => $this->notes,
            'next_follow_up_date' => $this->next_follow_up_date,
            'status' => $this->status,
            'contacted_by' => $this->contactedBy?->name,
            'contacted_by_id' => $this->contacted_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
