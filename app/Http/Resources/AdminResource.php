<?php

namespace App\Http\Resources;

use App\Utils\Resources\BaseResource;
use Illuminate\Http\Request;

class AdminResource extends BaseResource
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
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
        ];
        return array_merge($attributes, $customFields);
    }
}
