<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Utils\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ProductController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Product::class,
            resource: ProductResource::class,
            loadRelations: ['category'],
            validation: [
                'countable' => ['nullable', 'boolean'],
                'category_id' => ['required', 'exists:categories,id'],
                'name' => ['required', 'string', 'max:255', 'unique:products'],
                'primary_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
                'gallery' => ['required', 'array'],
                'gallery.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
                'english_name' => ['required', 'string', 'max:255', 'unique:products'],
                'seo_title' => ['required', 'string', 'max:255'],
                'seo_meta_description' => ['required', 'string'],
                'seo_keywords' => ['required', 'array'],
                'seo_keywords.*' => ['required', 'string'],
                'description' => ['required', 'string'],
                'price' => ['required', 'numeric', 'min:1'],
                'stock' => ['nullable', 'integer', 'min:0'],
                'discount' => ['nullable', 'numeric', 'min:0'],
            ],
            validation_extensions: [
                'store' => $this->ext(),
                'edit' => $this->ext(),
            ]
        );
    }

    private function ext(): array
    {
        return [
            'category_id' => function (Request $request, array $validated) {
                if (!isset($validated['category_id'])) {
                    return null;
                }
                $category = Category::query()->findOrFail($validated['category_id']);
                if (!isset($category->parent_id)) {
                    throw new UnprocessableEntityHttpException('You can\'t add to main categories');
                }
                return $validated['category_id'];
            },
            'primary_image' => fn(Request $request, array $validated) => isset($validated['primary_image']) ? $request->file('primary_image')->store('products', 'public') : null,
            'gallery' => function (Request $request, array $validated) {
                if (isset($validated['gallery']) && count($validated['gallery']) > 0) {
                    $valid = $validated['gallery'];
                    for ($i = 0; $i < count($valid); $i++) {
                        $valid[$i] = $valid[$i]->store('products', 'public');
                    }
                    return json_encode($valid);
                }
                return null;
            },
            'seo_keywords' => fn(Request $request, array $validated) => isset($validated['seo_keywords']) ? json_encode($validated['seo_keywords']) : null,
            'seo_options' => fn(Request $request, array $validated) => json_encode(
                [
                    "seo_title" => $validated['seo_title'] ?? null,
                    "seo_meta_description" => $validated['seo_meta_description'] ?? null,
                    'seo_keywords' => $validated['seo_keywords'] ?? null
                ]
            ),
        ];
    }
}
