<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Utils\Controllers\BaseController;

class TestController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Category::class,
        );
    }
}
