<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'member_name' => $this->member?->name,
            'amount' => $this->amount,
            'contribution_date' => $this->contribution_date,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'recorded_by' => $this->recordedBy?->name,
            'recorded_by_id' => $this->recorded_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
