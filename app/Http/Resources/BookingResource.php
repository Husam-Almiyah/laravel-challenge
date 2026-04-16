<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'scheduled_at' => $this->scheduled_at,
            'total_amount' => $this->total_amount,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'metadata' => $this->whenNotNull($this->metadata),
            'address' => new AddressResource($this->whenLoaded('address')),
            'items' => BookingItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items') ?? $this->items->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
