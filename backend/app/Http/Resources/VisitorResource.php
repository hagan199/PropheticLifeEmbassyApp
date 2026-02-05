<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'source' => $this->source,
            'ministry_interests' => $this->ministry_interests,
            'status' => $this->status,
            'first_visit_date' => $this->first_visit_date,
            'notes' => $this->notes,
            'follow_ups' => FollowUpResource::collection($this->whenLoaded('followUps')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
