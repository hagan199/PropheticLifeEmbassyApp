<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'member_name' => $this->member?->name,
            'service_type' => $this->service_type,
            'service_date' => $this->service_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'recorded_by' => $this->recordedBy?->name,
            'recorded_by_id' => $this->recorded_by,
            'approved_by' => $this->approvedBy?->name,
            'approved_at' => $this->approved_at?->toISOString(),
            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
