<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'price' => $this->price,
            'quantity' => $this->quantity,
            'subtotal' => $this->price * $this->quantity,
            'itemable_type' => $this->itemable_type,
            'itemable' => $this->whenLoaded('itemable', function () {
                return [
                    'id' => $this->itemable->id,
                    'name' => $this->itemable->name,
                    'type' => class_basename($this->itemable),
                    'category' => $this->itemable->category?->name ?? null,
                ];
            }),
        ];
    }
}
