<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'trial_days' => $this->trial_days,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'is_trial_available' => $this->trial_days > 0,
        ];
    }
}
