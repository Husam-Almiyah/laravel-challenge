<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $daysRemaining = $this->ends_at ? now()->diffInDays($this->ends_at, false) : 0;

        return [
            'id' => $this->id,
            'plan' => $this->whenLoaded('plan', function () {
                return [
                    'id' => $this->plan->id,
                    'name' => $this->plan->name,
                    'description' => $this->plan->description,
                ];
            }),
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'status' => $this->status,
            'days_remaining' => max(0, $daysRemaining),
            'is_trial' => $this->is_trial,
            'created_at' => $this->created_at,
        ];
    }
}
