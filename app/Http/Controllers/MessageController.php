<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\Ticket;
use App\Utils\Controllers\Controller;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
        ]);
        $validated['user_id'] = $request->user('user')->id;
        $ticket = Ticket::query()->where('user_id', $validated['user_id'])->find($validated['ticket_id']);
        if (!$ticket) {
            return response()->json(["error" => "Unauthorized"]);
        }
        Message::query()->create($validated);
        return response()->json(["message" => "Message sent"]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
        ]);
        $ticket = Ticket::query()->where('user_id', $request->user('user')->id)->find($validated['ticket_id']);
        if (!$ticket) {
            return response()->json(["error" => "Unauthorized."]);
        }
        return FunctionUtils::automatedPaginationWithBuilder
        (
            $request,
            Message::with(['user', 'admin', 'ticket'])
                ->where('ticket_id', $validated['ticket_id']),
            MessageResource::class
        );
    }

    public function show(Request $request, $id): JsonResponse
    {
        $message = Message::query()->whereRelation('ticket', 'user_id', $request->user('user')->id)->find($id);
        if (!$message) {
            return response()->json(["error" => "Unauthorized."]);
        }
        return response()->json(MessageResource::make($message));
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $message = Message::query()->where('user_id', $request->user('user')->id)->find($id);

        if (!$message) {
            return response()->json(["error" => "Unauthorized."]);
        }

        $message->update($validated);

        return response()->json(["message" => "Message edited"]);
    }

    public function editAdmin(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);
        $message = Message::query()->where('admin_id', $request->user('admin')->id)->findOrFail($id);
        if (!$message) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $message->update($validated);

        return response()->json(["message" => "Message edited"]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $message = Message::query()->where('user_id', $request->user('user')->id)->findOrFail($id);
        if (!$message) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $message->delete();
        return response()->json(["message" => "Message deleted"]);
    }

    public function storeAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
        ]);
        $validated['admin_id'] = $request->user('admin')->id;
        Message::query()->create($validated);
        return response()->json(["message" => "Message sent"]);
    }

    public function destroyAdmin(Request $request, $id): JsonResponse
    {
        $message = Message::query()->where('admin_id', $request->user('admin')->id)->findOrFail($id);
        if (!$message) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $message->delete();
        return response()->json(["message" => "Message deleted"]);
    }

    public function indexAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
        ]);
        return FunctionUtils::automatedPaginationWithBuilder
        (
            $request,
            Message::with(['user', 'admin', 'ticket'])
                ->where('ticket_id', $validated['ticket_id']),
            MessageResource::class
        );
    }

    public function showAdmin($id): JsonResponse
    {
        $message = Message::query()->findOrFail($id);
        return response()->json(MessageResource::make($message));
    }
}
