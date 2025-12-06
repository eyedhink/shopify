<?php

namespace App\Utils\Controllers\ControllerTraits;

use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Show
{
    /**
     * @throws AccessDeniedException
     */
    public
    function show($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-show")) {
            throw new AccessDeniedException();
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
}
