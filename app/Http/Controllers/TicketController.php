<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Utils\Controllers\Controller;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        $validated['user_id'] = $request->user('user')->id;

        if (!isset($validated['user_id'])) {
            return response()->json(["error" => "User not found"]);
        }

        Ticket::query()->create($validated);
        return response()->json(["message" => "Your ticket has been created"]);
    }

    public function index(Request $request): JsonResponse
    {
        return FunctionUtils::automatedPaginationWithBuilder
        (
            $request,
            Ticket::with(['user', 'messages'])
                ->where('user_id', $request->user('user')->id),
            TicketResource::class
        );
    }

    public function indexAdmin(Request $request): JsonResponse
    {
        return FunctionUtils::automatedPaginationWithBuilder
        (
            $request,
            Ticket::with(['user', 'messages']),
            TicketResource::class
        );
    }

    public function show(Request $request, $id): JsonResponse
    {
        return response()->json(
            TicketResource::make
            (
                Ticket::with(['user', 'messages'])
                    ->where('user_id', $request->user('user')->id)
                    ->findOrFail($id)
            )
        );
    }

    public function showAdmin($id): JsonResponse
    {
        return response()->json(
            TicketResource::make
            (
                Ticket::with(['user', 'messages'])
                    ->findOrFail($id)
            )
        );
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $ticket = Ticket::query()->where('user_id', $request->user('user')->id)->findOrFail($id);

        if (!$ticket) {
            return response()->json(["error" => "Unauthorized"]);
        }

        $ticket->update($validated);

        return response()->json(["message" => "Your ticket has been updated"]);
    }

    public function editAdmin($id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        Ticket::query()->findOrFail($id)->update($validated);

        return response()->json(["message" => "Your ticket has been updated"]);
    }

    public function delete(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::query()->where('user_id', $request->user('user')->id)->findOrFail($id);

        if (!$ticket) {
            return response()->json(["error" => "Unauthorized"]);
        }

        $ticket->delete();
        return response()->json(["message" => "Your ticket has been deleted"]);
    }

    public function deleteAdmin($id): JsonResponse
    {
        $ticket = Ticket::query()->findOrFail($id);
        $ticket->delete();
        return response()->json(["message" => "Your ticket has been deleted"]);
    }

    public function restore(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::withTrashed()->where('user_id', $request->user('user')->id)->findOrFail($id);

        if (!$ticket) {
            return response()->json(["error" => "Unauthorized"]);
        }

        $ticket->restore();
        return response()->json(["message" => "Your ticket has been restored"]);
    }

    public function restoreAdmin($id): JsonResponse
    {
        $ticket = Ticket::withTrashed()->findOrFail($id);
        $ticket->restore();
        return response()->json(["message" => "Your ticket has been restored"]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::withTrashed()->where('user_id', $request->user('user')->id)->findOrFail($id);

        if (!$ticket) {
            return response()->json(["error" => "Unauthorized"]);
        }

        $ticket->forceDelete();
        return response()->json(["message" => "Your ticket has been perma deleted"]);
    }
}
