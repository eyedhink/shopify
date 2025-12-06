<?php

namespace App\Utils\Controllers\ControllerTraits;

use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Store
{
    use MatchIds;

    /**
     * @throws AccessDeniedException
     */
    public
    function store(Request $request): JsonResponse
    {
        if ($this->ability_system && (!isset($this->ability_system_blacklist) || !array_search('store', $this->ability_system_blacklist)) && !FunctionUtils::isAuthorized($request->user($this->ability_guard), $this->ability_prefix . "-store")) {
            throw new AccessDeniedException();
        }
        $validated = $request->validate($this->validation_create);
        $custom_extensions = array_search("store", array_keys($this->validation_extensions));
        if ($custom_extensions || $custom_extensions === 0) {
            foreach ($this->validation_extensions['store'] as $key => $value) {
                $v = $value($request, $validated);
                if ($v != null) {
                    $validated[$key] = $v;
                }
            }
        }
        foreach ($this->match_ids as $key => $value) {
            if ($key == 'store') {
                if (!$this->matchIds($validated, $value)) {
                    throw new AccessDeniedException();
                }
            }
        }
        $this->model::query()->create($validated);
        return response()->json(["message" => last(explode('\\', get_class(new $this->model()))) . " created successfully"]);
    }
}
