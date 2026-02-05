<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_type_id' => $this->expense_type_id,
            'expense_type_name' => $this->expenseType?->name,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'description' => $this->description,
            'status' => $this->status,
            'receipt_url' => $this->receipt_url,
            'submitted_by' => $this->submittedBy?->name,
            'submitted_by_id' => $this->submitted_by,
            'approved_by' => $this->approvedBy?->name,
            'approved_at' => $this->approved_at?->toISOString(),
            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
