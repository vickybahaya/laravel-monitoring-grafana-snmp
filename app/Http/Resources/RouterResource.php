<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ip_address' => $this->ip_address,
            'port' => $this->port,
            'username' => $this->username,
            'category' => new RouterCategoryResource($this->whenLoaded('category')),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location' => $this->location,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
            'last_checked_at' => $this->last_checked_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
