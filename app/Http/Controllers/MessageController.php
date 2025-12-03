<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\Ticket;
use App\Utils\Controllers\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Message::class,
            resource: MessageResource::class,
            loadRelations: ['user', 'ticket'],
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
                    'user_id' => fn(Request $request, array $validated) => $request->user('user')->id,
                ],
                'index' => [
                    'user_id' => fn(Request $request, array $validated) => $request->user('user')->id,
                ]
            ],
            selection_query: fn(Request $request): Builder => Message::with(['user', 'ticket'])->where('user_id', $request->user('user')->id),
            selection_query_blacklist: [
                'index'
            ],
            selection_query_replace: [
                'index' => fn(Request $request, array $validated): Builder => Message::with(['user', 'ticket'])
                    ->where('ticket_id', $validated['ticket_id']),
            ],
            match_ids: [
                'store' => ['user_id', 'ticket_id', Ticket::class],
                'index' => ['user_id', 'ticket_id', Ticket::class],
            ]
        );
    }
}
