<?php

namespace App\Utils\Controllers\ControllerTraits;

use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Index
{
    use MatchIds;

    /**
     * @throws AccessDeniedException
     */
    public
    function index(Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-index")) {
            throw new AccessDeniedException();
        }
        $validated = $request->validate($this->validation_index);
        $custom_extensions = array_search("index", array_keys($this->validation_extensions));
        if ($custom_extensions || $custom_extensions === 0) {
            foreach ($this->validation_extensions['index'] as $key => $value) {
                $v = $value($request, $validated);
                if ($v != null) {
                    $validated[$key] = $v;
                }
            }
        }
        foreach ($this->match_ids as $key => $value) {
            if ($key == 'index') {
                if (!$this->matchIds($validated, $value)) {
                    throw new AccessDeniedException();
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
}
