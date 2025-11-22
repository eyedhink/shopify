<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
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
            'items' => ItemResource::collection($this->whenLoaded('items')),
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
        ];
        return array_merge($attributes, $customFields);
    }
}
