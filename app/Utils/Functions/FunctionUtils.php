<?php

namespace App\Utils\Functions;

use App\Models\Admin;
use App\Utils\Resources\BaseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FunctionUtils
{
    public static function validatePaginationRequest(Request $request): array
    {
        return $request->validate([
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ["nullable", "integer", "min:1"],
        ]);
    }

    public static function makePaginationInfo(LengthAwarePaginator $models, array $validated, int $limit = 10): array
    {
        $total_pages = ceil($models->total() / $limit);
        return [
            "total_items" => $models->total(),
            "total_pages" => $total_pages,
            "current_page" => $validated["page"],
            "per_page" => $limit,
            "has_next_page" => ($total_pages - $validated['page']) > 0,
            "has_previous_page" => ($validated['page'] - 1) > 0,
            "next_page" => $validated['page'] + 1,
            "previous_page" => $validated['page'] - 1
        ];
    }

    public static function makePaginationInfoNonTotal(LengthAwarePaginator $models, array $validated, int $limit = 10): array
    {
        $total_pages = ceil(count($models) / $limit);
        return [
            "total_items" => count($models),
            "total_pages" => $total_pages,
            "current_page" => $validated["page"],
            "per_page" => $limit,
            "has_next_page" => ($total_pages - $validated['page']) > 0,
            "has_previous_page" => ($validated['page'] - 1) > 0,
            "next_page" => $validated['page'] + 1,
            "previous_page" => $validated['page'] - 1
        ];
    }

    /**
     * @param array $validated
     * @template TModel of Model
     * @param class-string<TModel> $model
     * @template TResource of BaseResource
     * @param class-string<TResource> $resource
     * @return JsonResponse
     */
    public static function handlePagination(array $validated, string $model, string $resource): JsonResponse
    {
        $limit = $validated['limit'] ?? 10;
        $models = $model::query()->paginate($limit, page: $validated['page']);
        $pagination_info = FunctionUtils::makePaginationInfo($models, $validated, $limit);
        return response()->json(["data" => $resource::collection($models), "pagination_info" => $pagination_info]);
    }

    /**
     * @template TModel of Model
     * @param Request $request
     * @param class-string<TModel> $model
     * @param class-string<TResource> $resource
     * @return JsonResponse
     * @template TResource of BaseResource
     */
    public static function automatedPagination(Request $request, string $model, string $resource): JsonResponse
    {
        return FunctionUtils::handlePagination(FunctionUtils::validatePaginationRequest($request), $model, $resource);
    }

    /**
     * @template TModel of Model
     * @param Request $request
     * @param Builder $query
     * @param class-string<TResource> $resource
     * @return JsonResponse
     * @template TResource of BaseResource
     */
    public static function automatedPaginationWithBuilder(Request $request, Builder $query, string $resource): JsonResponse
    {
        $validated = FunctionUtils::validatePaginationRequest($request);
        $limit = $validated['limit'] ?? 10;
        $models = $query->paginate($limit, page: $validated['page']);
        $pagination_info = FunctionUtils::makePaginationInfo($models, $validated, $limit);
        return response()->json(["data" => $resource::collection($models), "pagination_info" => $pagination_info]);
    }

    public static function isAuthorized(Admin $admin, string $ability): bool
    {
        return $admin->is_main_admin || in_array($ability, $admin->abilities);
    }
}
