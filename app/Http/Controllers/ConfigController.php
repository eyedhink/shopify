<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Utils\Controllers\BaseController;

class ConfigController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Config::class,
            ability_prefix: 'config',
            validation: [
                'key' => ['required', 'string'],
                'value' => ['required', 'string'],
            ],
            custom_kws: [
                "edit" => "key"
            ]
        );
    }
}
