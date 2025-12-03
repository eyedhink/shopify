<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Utils\Controllers\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TicketController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Ticket::class,
            resource: TicketResource::class,
            loadRelations: ['user'],
            validation: [
                'title' => ['required', 'string'],
                'content' => ['required', 'string'],
            ],
            validation_extensions: [
                'store' => [
                    'user_id' => fn(Request $request, array $validated) => $request->user('user')->id,
                ]
            ],
            selection_query: fn(Request $request): Builder => Ticket::with(['user'])->where('user_id', $request->user('user')->id),
            selection_query_with_trashed: fn(Request $request): Builder => Ticket::withTrashed()->with(['user'])->where('user_id', $request->user('user')->id),
        );
    }
}
