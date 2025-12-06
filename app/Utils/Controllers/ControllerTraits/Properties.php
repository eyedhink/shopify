<?php

namespace App\Utils\Controllers\ControllerTraits;

trait Properties
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
}
