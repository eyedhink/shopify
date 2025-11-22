<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\Ticket;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
        ]);
        if (Auth::guard('user')->check()) {
            $validated['user_id'] = $request->user('user')->id;
            $ticket = Ticket::query()->findOrFail($validated['ticket_id']);
            if ($ticket->user_id != $request->user('user')->id) {
                return response()->json(["error" => "You are not allowed to create messages on this ticket."]);
            }
        }
        if (Auth::guard('admin')->check()) {
            $validated['admin_id'] = $request->user('admin')->id;
            if (!Utils::isAuthorized($request->user('admin'), 'message-store')) {
                return response()->json(["error" => "Unauthorized."]);
            }
        }
        if (!(isset($validated['admin_id']) || isset($validated['user_id']))) {
            return response()->json(["errors" => ["Sender is required"]]);
        }
        Message::query()->create($validated);
        return response()->json(["message" => "Message sent"]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
        ]);
        if (Auth::guard('user')->check()) {
            $ticket = Ticket::query()->findOrFail($validated['ticket_id']);
            if ($ticket->user_id != $request->user('user')->id) {
                return response()->json(["error" => "You are not allowed to index messages on this ticket."]);
            }
        }
        if (Auth::guard('admin')->check()) {
            if (!Utils::isAuthorized($request->user('admin'), 'message-index')) {
                return response()->json(["error" => "Unauthorized."]);
            }
        }
        return Utils::automatedPaginationWithBuilder
        (
            $request,
            Message::with(['user', 'admin', 'ticket'])
                ->where('ticket_id', $validated['ticket_id']),
            MessageResource::class
        );
    }

    public function show(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::query()->findOrFail($id);
        if (Auth::guard('user')->check()) {
            if ($ticket->user_id != $request->user('user')->id) {
                return response()->json(["error" => "You are not allowed to show messages on this ticket."]);
            }
        }
        if (Auth::guard('admin')->check()) {
            if (!Utils::isAuthorized($request->user('admin'), 'message-show')) {
                return response()->json(["error" => "Unauthorized."]);
            }
        }
        return response()->json(MessageResource::make(Message::query()->findOrFail($id)));
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $message = Message::query()->findOrFail($id);

        if (Auth::guard('user')->check()) {
            if ($message->user_id != $request->user('user')->id) {
                return response()->json(["error" => "You are not allowed to edit messages on this ticket."]);
            }
        }
        if (Auth::guard('admin')->check()) {
            if (($message->admin_id != $request->user('admin')->id) || !Utils::isAuthorized($request->user('admin'), 'message-edit')) {
                return response()->json(["error" => "You are not allowed to edit messages on this ticket."]);
            }
        }

        $message->update($validated);

        return response()->json(["message" => "Message edited"]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $message = Message::query()->findOrFail($id);

        if (Auth::guard('user')->check()) {
            if ($message->user_id != $request->user('user')->id) {
                return response()->json(["error" => "You are not allowed to delete messages on this ticket."]);
            }
        }
        if (Auth::guard('admin')->check()) {
            if (($message->admin_id != $request->user('admin')->id || !Utils::isAuthorized($request->user('admin'), 'message-delete'))) {
                return response()->json(["error" => "You are not allowed to delete messages on this ticket."]);
            }
        }
        $message->delete();
        return response()->json(["message" => "Message deleted"]);
    }
}
