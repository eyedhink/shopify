<?php

namespace App\Utils\Controllers\ControllerTraits;

use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Edit
{
    /**
     * @throws AccessDeniedException
     */
    public
    function edit($kw, Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-edit")) {
            throw new AccessDeniedException();
        }
        $validated = $request->validate($this->validation_update);
        $custom_extensions = array_search("edit", array_keys($this->validation_extensions));
        if ($custom_extensions || $custom_extensions === 0) {
            foreach ($this->validation_extensions['edit'] as $key => $value) {
                $v = $value($request, $validated);
                if ($v != null) {
                    $validated[$key] = $v;
                }
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
}
