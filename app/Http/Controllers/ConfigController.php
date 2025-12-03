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
