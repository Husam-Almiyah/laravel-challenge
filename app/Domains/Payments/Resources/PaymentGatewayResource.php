<?php

namespace App\Domains\Payments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'driver' => $this->driver,
            'currency' => $this->currency,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'rules' => $this->rules,
            'settings' => $this->settings,
        ];
    }
}
