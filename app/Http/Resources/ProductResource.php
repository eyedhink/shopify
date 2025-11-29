<?php

namespace App\Http\Resources;

use App\Utils\Resources\BaseResource;
use Illuminate\Http\Request;

class ProductResource extends BaseResource
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
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'items' => ItemResource::collection($this->whenLoaded('items')),
        ];
        return array_merge($attributes, $customFields);
    }
}
