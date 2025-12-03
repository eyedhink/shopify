<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Utils\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CategoryController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Category::class,
            resource: CategoryResource::class,
            loadRelations: ['parent', 'children', 'products'],
            validation: [
                'name' => ['required', 'string', 'unique:categories,name', 'max:255'],
                'parent_id' => ['nullable', 'exists:categories,id'],
                'seo_title' => ['required', 'string', 'max:255'],
                'seo_meta_description' => ['required', 'string'],
                'seo_keywords' => ['required', 'array'],
                'seo_keywords.*' => ['required', 'string']
            ],
            validation_extensions: [
                'store' => $this->ext(),
                'edit' => $this->ext()
            ]
        );
    }

    private function ext(): array
    {
        return [
            'seo_keywords' => fn(Request $request, array $validated) => isset($validated['seo_keywords']) ? json_encode($validated['seo_keywords']) : null,
            'seo_options' => fn(Request $request, array $validated) => json_encode(
                [
                    "seo_title" => $validated['seo_title'] ?? null,
                    "seo_meta_description" => $validated['seo_meta_description'] ?? null,
                    'seo_keywords' => $validated['seo_keywords'] ?? null
                ]
            ),
            'depth' => function (Request $request, array $validated) {
                if (isset($validated['parent_id'])) {
                    $parent = Category::query()->findOrFail($validated['parent_id']);
                    if ($parent->depth > 5) {
                        throw new UnprocessableEntityHttpException('Maximum number of subcategory depth reached');
                    }
                    return $parent->depth + 1;
                } else {
                    return 0;
                }
            },
        ];
    }
}
