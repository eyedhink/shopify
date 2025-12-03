<?php

namespace App\Utils\Controllers;

use AllowDynamicProperties;
use App\Utils\Functions\FunctionUtils;
use App\Utils\Resources\BaseResource;
use http\Exception\UnexpectedValueException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[AllowDynamicProperties] class BaseController extends Controller
{
    private string $model;
    private string $resource;
    private array $loadRelations;
    private bool $ability_system;
    private string $ability_guard;
    private string $ability_prefix;
    private array $ability_system_blacklist;
    private array $validation;
    private array $validation_create;
    private array $validation_index;
    private array $validation_update;
    private array $validation_extensions;
    private array $custom_kws;
    private array $selection_query_blacklist;
    private array $selection_query_replace;
    private array $match_ids;


    /**
     * @template TModel of Model
     * @param class-string<TModel> $model
     * @param class-string<TResource> $resource
     * @param array $loadRelations
     * @param bool $ability_system
     * @param string $ability_guard
     * @param string $ability_prefix
     * @param array $ability_system_blacklist
     * @param array $validation
     * @param array $validation_create
     * @param array $validation_update
     * @param array $custom_kws
     * @param array $validation_extensions
     * @param (callable(Request $request): Builder)|null $selection_query
     * @param (callable(Request $request): Builder)|null $selection_query_with_trashed
     * @param array $match_ids
     * @param array $validation_index
     * @param array $selection_query_blacklist
     * @param array $selection_query_replace
     * @template TResource of BaseResource
     */
    public function __construct
    (
        string        $model = Model::class,
        string        $resource = BaseResource::class,
        array         $loadRelations = [],
        bool          $ability_system = false,
        string        $ability_guard = "admin",
        string        $ability_prefix = "",
        array         $ability_system_blacklist = [],
        array         $validation = [],
        array         $validation_create = [],
        array         $validation_index = [],
        array         $validation_update = [],
        array         $validation_extensions = [],
        array         $custom_kws = [],
        callable|null $selection_query = null,
        callable|null $selection_query_with_trashed = null,
        array         $selection_query_blacklist = [],
        array         $selection_query_replace = [],
        array         $match_ids = [],
    )
    {
        $this->model = $model;
        $this->resource = $resource;
        $this->loadRelations = $loadRelations;
        $this->ability_system = $ability_system;
        $this->ability_guard = $ability_guard;
        $this->ability_prefix = $ability_prefix;
        $this->ability_system_blacklist = $ability_system_blacklist;
        $this->validation = $validation;
        if (count($validation_create) < 1) {
            foreach ($this->validation as $key => $value) {
                $validation_create[$key] = $value;
            }
        }
        $this->validation_create = $validation_create;
        $this->validation_index = $validation_index;
        if (count($validation_update) < 1) {
            foreach ($this->validation as $key => $value) {
                $t = array_search("required", $value);
                if (($t || $t === 0) && count($this->validation) > 1) {
                    $value[$t] = "nullable";
                }
                $validation_update[$key] = $value;
            }
        }
        $this->validation_update = $validation_update;
        $this->validation_extensions = $validation_extensions;
        $this->custom_kws = $custom_kws;
        $this->selection_query = $selection_query != null ? fn(Request $request) => $selection_query($request) : null;
        $this->selection_query_with_trashed = $selection_query_with_trashed != null ? fn(Request $request) => $selection_query_with_trashed($request) : null;
        $this->selection_query_blacklist = $selection_query_blacklist;
        $this->selection_query_replace = $selection_query_replace;
        $this->match_ids = $match_ids;
    }

    public
    function store(Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-store")) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate($this->validation_create);
        $custom_extensions = array_search("store", array_keys($this->validation_extensions));
        if ($custom_extensions || $custom_extensions === 0) {
            foreach ($this->validation_extensions['store'] as $key => $value) {
                $validated[$key] = $value($request, $validated);
            }
        }
        foreach ($this->match_ids as $key => $value) {
            if ($key == 'store') {
                if (!$this->match_ids($validated, $value)) {
                    return response()->json(["error" => "Unauthorized."]);
                }
            }
        }
        $this->model::query()->create($validated);
        return response()->json(["message" => last(explode('\\', get_class(new $this->model()))) . " created successfully"]);
    }

    public
    function index(Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-index")) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate($this->validation_index);
        $custom_extensions = array_search("index", array_keys($this->validation_extensions));
        if ($custom_extensions || $custom_extensions === 0) {
            foreach ($this->validation_extensions['index'] as $key => $value) {
                $validated[$key] = $value($request, $validated);
            }
        }
        foreach ($this->match_ids as $key => $value) {
            if ($key == 'index') {
                if (!$this->match_ids($validated, $value)) {
                    return response()->json(["error" => "Unauthorized."]);
                }
            }
        }
        $query = $this->selection_query != null && !in_array('index', $this->selection_query_blacklist) ? ($this->selection_query)($request) : (count($this->loadRelations) < 1 ? $this->model::query() : $this->model::with($this->loadRelations));
        foreach ($this->selection_query_replace as $key => $value) {
            if ($key == 'index') {
                $query = $value($request, $validated);
            }
        }
        return FunctionUtils::automatedPaginationWithBuilder($request, $query, $this->resource);
    }

    public
    function show($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-show")) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $query = $this->selection_query != null && !in_array('show', $this->selection_query_blacklist) ? ($this->selection_query)($request) : (count($this->loadRelations) < 1 ? $this->model::query() : $this->model::with($this->loadRelations));
        foreach ($this->selection_query_replace as $key => $value) {
            if ($key == 'show') {
                $query = $value($request);
            }
        }
        $custom_kw = array_search("show", array_keys($this->custom_kws));
        return response()->json($this->resource::make($query->firstWhere(($custom_kw || $custom_kw === 0) ? $this->custom_kws["show"] : "id", $kw)));
    }

    public
    function edit($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-edit")) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate($this->validation_update);
        $custom_extensions = array_search("edit", array_keys($this->validation_extensions));
        if ($custom_extensions || $custom_extensions === 0) {
            foreach ($this->validation_extensions['edit'] as $key => $value) {
                $validated[$key] = $value($request, $validated);
            }
        }
        $custom_kw = array_search("edit", array_keys($this->custom_kws));
        $query = $this->selection_query != null && !in_array('edit', $this->selection_query_blacklist) ? ($this->selection_query)($request) : $this->model::query();
        foreach ($this->selection_query_replace as $key => $value) {
            if ($key == 'edit') {
                $query = $value($request, $validated);
            }
        }
        $model = $query->firstWhere(($custom_kw || $custom_kw === 0) ? $this->custom_kws["edit"] : "id", $kw);
        $model->update($validated);
        return response()->json(["message" => last(explode('\\', get_class(new $this->model()))) . " updated successfully"]);
    }

    public
    function delete($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-delete")) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $custom_kw = array_search("delete", array_keys($this->custom_kws));
        $query = $this->selection_query != null && !in_array('delete', $this->selection_query_blacklist) ? ($this->selection_query)($request) : $this->model::query();
        foreach ($this->selection_query_replace as $key => $value) {
            if ($key == 'delete') {
                $query = $value($request);
            }
        }
        $query->firstWhere(($custom_kw || $custom_kw === 0) ? $this->custom_kws["delete"] : "id", $kw)->delete();
        return response()->json(["message" => last(explode('\\', get_class(new $this->model()))) . " deleted successfully"]);
    }

    public
    function restore($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('restore', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-restore")) {
            return response()->json(["error" => "Unauthorized."]);
        }
        if (!in_array(SoftDeletes::class, class_uses_recursive($this->model))) {
            throw new UnexpectedValueException('The model class must use the SoftDeletes trait.');
        }
        $custom_kw = array_search("restore", array_keys($this->custom_kws));
        $query = $this->selection_query_with_trashed != null && !in_array('restore', $this->selection_query_blacklist) ? ($this->selection_query_with_trashed)($request) : $this->model::withTrashed();
        foreach ($this->selection_query_replace as $key => $value) {
            if ($key == 'restore') {
                $query = $value($request);
            }
        }
        $query->firstWhere(($custom_kw || $custom_kw === 0) ? $this->custom_kws["restore"] : "id", $kw)->restore();
        return response()->json(["message" => last(explode('\\', get_class(new $this->model()))) . " restored successfully"]);
    }

    public
    function destroy($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-destroy")) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $custom_kw = array_search("destroy", array_keys($this->custom_kws));
        $query = $this->selection_query_with_trashed != null && !in_array('destroy', $this->selection_query_blacklist) ? ($this->selection_query_with_trashed)($request) : ($this->selection_query != null ? ($this->selection_query)($request) : $this->model::query());
        foreach ($this->selection_query_replace as $key => $value) {
            if ($key == 'destroy') {
                $query = $value($request);
            }
        }
        $query->firstWhere(($custom_kw || $custom_kw === 0) ? $this->custom_kws["destroy"] : "id", $kw)->forceDelete();
        return response()->json(["message" => last(explode('\\', get_class(new $this->model()))) . " permanently deleted successfully"]);
    }

    private function match_ids(array $validated, array $value)
    {
        $queried_id = $value[0];
        $query_id = $value[1];
        $query_model = $value[2];
        return $query_model::query()->where($queried_id, $validated[$queried_id])->find($validated[$query_id]);
    }
}
