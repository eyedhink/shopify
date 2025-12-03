<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Utils\Controllers\BaseController;
use Illuminate\Http\Request;

class TestController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Message::class,
            validation: [
                'content' => ['required', 'string'],
                'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
            ],
            validation_extensions: [
                'store' => [
                    'user_id' => fn(Request $request, array $validated) => $request->user('user')->id,
                ]
            ],
        );
    }
}
