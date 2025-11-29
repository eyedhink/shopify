<?php

namespace App\Http\Resources;

use App\Utils\Resources\BaseResource;
use Illuminate\Http\Request;

class CategoryResource extends BaseResource
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
            'parent' => CategoryResource::make($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
        return array_merge($attributes, $customFields);
    }
}
