<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Utils\Controllers\BaseController;

class TicketControllerAdmin extends BaseController
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
            ]
        );
    }
}
