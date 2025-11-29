<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\Controllers\Controller;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if (Auth::guard('user')->check()) {
            return FunctionUtils::automatedPaginationWithBuilder
            (
                $request,
                Ticket::with(['user', 'messages'])
                    ->where('user_id', $request->user('user')->id),
                TicketResource::class
            );
        } else if (Auth::guard('admin')->check() && FunctionUtils::isAuthorized($request->user('admin'), 'ticket-index')) {
            return FunctionUtils::automatedPaginationWithBuilder
            (
                $request,
                Ticket::with(['user', 'messages']),
                TicketResource::class
            );
        } else {
            return response()->json(["error" => "Unauthorized"]);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        if (Auth::guard('user')->check()) {
            return response()->json(
                TicketResource::make
                (
                    Ticket::with(['user', 'messages'])
                        ->where('user_id', $request->user('user')->id)
                        ->findOrFail($id)
                )
            );
        } else if (Auth::guard('admin')->check() && FunctionUtils::isAuthorized($request->user('admin'), 'ticket-show')) {
            return response()->json(
                TicketResource::make
                (
                    Ticket::with(['user', 'messages'])
                        ->findOrFail($id)
                )
            );
        } else {
            return response()->json(["error" => "Unauthorized"]);
        }
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string'],
            'content' => ['sometimes', 'string'],
        ]);

        $ticket = Ticket::query()->findOrFail($id);

        if (Auth::guard('admin')->check() && !FunctionUtils::isAuthorized($request->user('admin'), 'ticket-edit')) {
            return response()->json(['error' => 'Unauthorized']);
        } else if (Auth::guard('user')->check()) {
            $user = User::query()->findOrFail($request->user('user')->id);
            if ($user->id != $ticket->user_id) {
                return response()->json(['error' => 'Unauthorized']);
            }
        }

        Ticket::query()->findOrFail($id)->update($validated);

        return response()->json(["message" => "Your ticket has been updated"]);
    }

    public function delete(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::query()->findOrFail($id);

        if (Auth::guard('admin')->check() && !FunctionUtils::isAuthorized($request->user('admin'), 'ticket-delete')) {
            return response()->json(['error' => 'Unauthorized']);
        } else if (Auth::guard('user')->check()) {
            $user = User::query()->findOrFail($request->user('user')->id);
            if ($user->id != $ticket->user_id) {
                return response()->json(['error' => 'Unauthorized']);
            }
        }

        $ticket->delete();
        return response()->json(["message" => "Your ticket has been deleted"]);
    }

    public function restore(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::withTrashed()->findOrFail($id);

        if (Auth::guard('admin')->check() && !FunctionUtils::isAuthorized($request->user('admin'), 'ticket-restore')) {
            return response()->json(['error' => 'Unauthorized']);
        } else if (Auth::guard('user')->check()) {
            $user = User::query()->findOrFail($request->user('user')->id);
            if ($user->id != $ticket->user_id) {
                return response()->json(['error' => 'Unauthorized']);
            }
        }

        $ticket->restore();
        return response()->json(["message" => "Your ticket has been restored"]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::withTrashed()->findOrFail($id);

        if (Auth::guard('admin')->check() && !FunctionUtils::isAuthorized($request->user('admin'), 'ticket-force-delete')) {
            return response()->json(['error' => 'Unauthorized']);
        } else if (Auth::guard('user')->check()) {
            $user = User::query()->findOrFail($request->user('user')->id);
            if ($user->id != $ticket->user_id) {
                return response()->json(['error' => 'Unauthorized']);
            }
        }

        $ticket->forceDelete();
        return response()->json(["message" => "Your ticket has been perma deleted"]);
    }
}
