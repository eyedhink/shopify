<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Utils\Controllers\Controller;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
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
        ]);
        $category = Category::query()->findOrFail($validated['category_id']);
        if (!isset($category->parent_id)) {
            return response()->json(["error" => "You can't add to main categories"]);
        }
        $validated['primary_image'] = $request->file('primary_image')->store('products', 'public');
        for ($i = 0; $i < count($validated['gallery']); $i++) {
            $validated['gallery'][$i] = $validated['gallery'][$i]->store('products', 'public');
        }
        $validated['gallery'] = json_encode($validated['gallery']);
        $validated['seo_keywords'] = json_encode($validated['seo_keywords']);
        $validated['seo_options'] = json_encode(
            [
                "seo_title" => $validated['seo_title'],
                "seo_meta_description" => $validated['seo_meta_description'],
                'seo_keywords' => $validated['seo_keywords']
            ]
        );
        Product::query()->create($validated);

        return response()->json(["message" => "Product created"]);
    }

    public function index(Request $request): JsonResponse
    {
        return FunctionUtils::automatedPaginationWithBuilder($request, Product::with(['category']), ProductResource::class);
    }

    public function show($id): JsonResponse
    {
        return response()->json(ProductResource::make(Product::with(['category'])->findOrFail($id)));
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'countable' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['nullable', 'string', 'max:255', 'unique:products'],
            'primary_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            'english_name' => ['nullable', 'string', 'max:255', 'unique:products'],
            'seo_options' => ['nullable', 'array'],
            'seo_options.seo_title' => ['nullable', 'string', 'max:255'],
            'seo_options.seo_meta_description' => ['nullable', 'string'],
            'seo_options.seo_keywords' => ['nullable', 'array'],
            'seo_options.seo_keywords.*' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'decimal', 'min:1'],
            'discount' => ['nullable', 'decimal', 'min:0'],
        ]);
        if (isset($validated['category_id'])) {
            $category = Category::query()->findOrFail($validated['category_id']);
            if (!isset($category->parent_id)) {
                return response()->json(["error" => "You can't add to main categories"]);
            }
        }
        if (isset($validated['primary_image'])) {
            $validated['primary_image'] = $request->file('primary_image')->store('products', 'public');
        }
        if (isset($validated['gallery']) && count($validated['gallery']) > 0) {
            for ($i = 0; $i < count($validated['gallery']); $i++) {
                $validated['gallery'][$i] = $validated['gallery'][$i]->store('products', 'public');
            }
            $validated['gallery'] = json_encode($validated['gallery']);
        }
        if (isset($validated['seo_keywords'])) {
            $validated['seo_keywords'] = json_encode($validated['seo_keywords']);
        }
        $validated['seo_options'] = json_encode(
            [
                "seo_title" => $validated['seo_title'] ?? null,
                "seo_description" => $validated['seo_description'] ?? null,
                'seo_keywords' => $validated['seo_keywords'] ?? null
            ]
        );
        Product::query()->findOrFail($id)->update($validated);

        return response()->json(["message" => "Product updated"]);
    }

    public function delete($id): JsonResponse
    {
        Product::query()->findOrFail($id)->delete();
        return response()->json(["message" => "Product deleted"]);
    }

    public function restore($id): JsonResponse
    {
        Product::withTrashed()->findOrFail($id)->restore();
        return response()->json(["message" => "Product restored"]);
    }

    public function destroy($id): JsonResponse
    {
        Product::withTrashed()->findOrFail($id)->forceDelete();
        return response()->json(["message" => "Product deleted"]);
    }
}
