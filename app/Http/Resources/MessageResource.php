<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributes = parent::toArray($request);
        $customFields = [
            'user' => UserResource::make($this->whenLoaded('user')),
            'admin' => AdminResource::make($this->whenLoaded('admin')),
            'ticket' => TicketResource::make($this->whenLoaded('ticket')),
        ];
        return array_merge($attributes, $customFields);
    }
}
