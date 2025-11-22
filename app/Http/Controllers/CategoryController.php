<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'category-store')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:categories,name', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'seo_title' => ['required', 'string', 'max:255'],
            'seo_meta_description' => ['required', 'string'],
            'seo_keywords' => ['required', 'array'],
            'seo_keywords.*' => ['required', 'string']
        ]);
        $validated['seo_keywords'] = json_encode($validated['seo_keywords']);
        $validated['seo_options'] = json_encode(
            [
                "seo_title" => $validated['seo_title'],
                "seo_meta_description" => $validated['seo_meta_description'],
                'seo_keywords' => $validated['seo_keywords']
            ]
        );
        if (isset($validated['parent_id'])) {
            $parent = Category::query()->findOrFail($validated['parent_id']);
            if ($parent->depth > 5) {
                return response()->json(["message" => "Maximum number of subcategories reached"]);
            }
            $validated['depth'] = $parent->depth + 1;
        } else {
            $validated['depth'] = 0;
        }
        Category::query()->create($validated);
        return response()->json(["message" => "Category created successfully"]);
    }

    public function index(Request $request): JsonResponse
    {
        return Utils::automatedPaginationWithBuilder($request, Category::with(["products", "parent", "children"]), CategoryResource::class);
    }

    public function show($id): JsonResponse
    {
        return response()->json(CategoryResource::make(Category::query()->findOrFail($id)));
    }

    public function edit(Request $request, $id): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'category-edit')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'unique:categories,name', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'seo_options' => ['sometimes', 'array'],
            'seo_options.seo_title' => ['sometimes', 'string', 'max:255'],
            'seo_options.seo_meta_description' => ['sometimes', 'string'],
            'seo_options.seo_keywords' => ['sometimes', 'array'],
            'seo_options.seo_keywords.*' => ['sometimes', 'string']
        ]);
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
        if (isset($validated['parent_id'])) {
            $parent = Category::query()->find($validated['parent_id']);
            if ($parent->depth > 5) {
                return response()->json(["message" => "Maximum number of subcategories reached"]);
            }
            $validated['depth'] = $parent->depth + 1;
        } else {
            $validated['depth'] = 0;
        }

        Category::query()->findOrFail($id)->update($validated);

        return response()->json(["message" => "Category updated successfully"]);
    }

    public function delete(Request $request, $id): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'category-delete')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        Category::query()->findOrFail($id)->delete();
        return response()->json("Category deleted successfully");
    }


    public function restore(Request $request, $id): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'category-restore')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        Category::withTrashed()->findOrFail($id)->restore();
        return response()->json("Category restored successfully");
    }


    public function destroy(Request $request, $id): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'category-destroy')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        Category::withTrashed()->findOrFail($id)->forceDelete();
        return response()->json("Category deleted successfully");
    }
}
