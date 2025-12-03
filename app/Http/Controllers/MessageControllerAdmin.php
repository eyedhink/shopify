<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Utils\Controllers\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MessageControllerAdmin extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Message::class,
            resource: MessageResource::class,
            loadRelations: ['admin', 'ticket'],
            validation: [
                'content' => ['required', 'string'],
                'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
            ],
            validation_index: [
                'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
            ],
            validation_update: [
                'content' => ['required', 'string'],
            ],
            validation_extensions: [
                'store' => [
                    'admin_id' => fn(Request $request, array $validated) => $request->user('admin')->id,
                ],
                'index' => [
                    'admin_id' => fn(Request $request, array $validated) => $request->user('admin')->id,
                ]
            ],
            selection_query: fn(Request $request): Builder => Message::with(['admin', 'ticket'])->where('admin_id', $request->user('admin')->id),
            selection_query_blacklist: [
                'index',
                'show'
            ],
            selection_query_replace: [
                'index' => fn(Request $request, array $validated): Builder => Message::with(['admin', 'ticket'])
                    ->where('ticket_id', $validated['ticket_id']),
            ]
        );
    }
}
