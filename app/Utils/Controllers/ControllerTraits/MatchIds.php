<?php

namespace App\Utils\Controllers\ControllerTraits;

trait MatchIds
{
    private function matchIds(array $validated, array $value)
    {
        $queried_id = $value[0];
        $query_id = $value[1];
        $query_model = $value[2];
        return $query_model::query()->where($queried_id, $validated[$queried_id])->find($validated[$query_id]);
    }
}
