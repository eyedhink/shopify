<?php

namespace App\Utils\Controllers\ControllerTraits;

use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Exceptions\ImpossibleRequestException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Restore
{
    /**
     * @throws AccessDeniedException|ImpossibleRequestException
     */
    public
    function restore($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('restore', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-restore")) {
            throw new AccessDeniedException();
        }
        if (!in_array(SoftDeletes::class, class_uses_recursive($this->model))) {
            throw new ImpossibleRequestException();
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
}
