<?php

namespace App\Utils\Controllers\ControllerTraits;

use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Delete
{
    /**
     * @throws AccessDeniedException
     */
    public
    function delete($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-delete")) {
            throw new AccessDeniedException();
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
}
