<?php

namespace App\Utils\Controllers;

use AllowDynamicProperties;
use App\Utils\Controllers\ControllerTraits\AIO;
use App\Utils\Resources\BaseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

#[AllowDynamicProperties] class BaseController extends Controller
{
    use AIO;

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
}
