<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Config;
use App\Models\Item;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (Auth::guard('user')->check()) {
            return Utils::automatedPaginationWithBuilder
            (
                $request,
                Order::with(["user", "address"])
                    ->where("user_id", $request->user('user')->id),
                OrderResource::class
            );
        } else if (Auth::guard('admin')->check() && Utils::isAuthorized($request->user('admin'), "order-index")) {
            return Utils::automatedPaginationWithBuilder
            (
                $request,
                Order::with(["user", "address"]),
                OrderResource::class
            );
        } else {
            return response()->json(["error" => "Unauthorized"]);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        if (Auth::guard('user')->check()) {
            return response()->json(
                OrderResource::make
                (
                    Order::with(["user", "address"])
                        ->where('user_id', $request->user('user')->id)
                        ->findOrFail($id)
                )
            );
        } else if (Auth::guard('admin')->check() && Utils::isAuthorized($request->user('admin'), "order-show")) {
            return response()->json(
                OrderResource::make
                (
                    Order::with(["user", "address"])
                        ->findOrFail($id)
                )
            );
        } else {
            return response()->json(["error" => "Unauthorized"]);
        }
    }

    public function pay(Request $request, $id): JsonResponse
    {
        $user = User::query()->findOrFail($request->user('user')->id);
        $order = Order::query()->findOrFail($id);
        if (!$order) {
            return response()->json(["error" => "Order not found"]);
        }
        if ($order->user_id != $user->id) {
            return response()->json(["error" => "Unauthorized"]);
        }
        if ($order->status != "pending payment") {
            return response()->json(["error" => "Already paid"]);
        }
        if ($user->credits < $order->total) {
            return response()->json(["error" => "Not enough credits"]);
        }
        $user->credits -= $order->total;
        $user->save();
        $order->status = "pending confirmation";
        $order->save();
        return response()->json(["message" => "Payment successful"]);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        $validated = $request->validate([
            "status" => ['required', 'string', 'in:processing,sending,completed'],
        ]);
        if (!Utils::isAuthorized($request->user('admin'), "order-update-status")) {
            return response()->json(["error" => "Unauthorized"]);
        }
        if ($validated['status'] === "processing" && $order->status !== "pending confirmation") {
            return response()->json(["error" => "Invalid status."]);
        }
        if ($validated['status'] === "sending" && $order->status !== "processing") {
            return response()->json(["error" => "Invalid status."]);
        }
        if ($validated['status'] === "completed" && $order->status !== "sending") {
            return response()->json(["error" => "Invalid status."]);
        }
        $order->update($validated);
        return response()->json(["message" => "Order status updated"]);
    }

    public function delete(Request $request, $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        if (!Utils::isAuthorized($request->user('admin'), "order-delete")) {
            return response()->json(["error" => "Unauthorized"]);
        }
        $order->delete();
        return response()->json(["message" => "Order deleted"]);
    }

    public function restore(Request $request, $id): JsonResponse
    {
        $order = Order::withTrashed()->findOrFail($id);
        if (!Utils::isAuthorized($request->user('admin'), "order-restore")) {
            return response()->json(["error" => "Unauthorized"]);
        }
        $order->restore();
        $order->status = "pending payment";
        $order->save();
        return response()->json(["message" => "Order restored"]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $order = Order::withTrashed()->findOrFail($id);
        if (Auth::guard('user')->check() && $order->user_id != $request->user('user')->id) {
            return response()->json(["error" => "Unauthorized"]);
        }
        if (Auth::guard('admin')->check() && !Utils::isAuthorized($request->user('admin'), "order-force-delete")) {
            return response()->json(["error" => "Unauthorized"]);
        }
        $order->forceDelete();
        return response()->json(["message" => "Order perma deleted"]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "user_id" => ['required', 'integer', 'exists:users,id'],
            "items" => ['required', 'array'],
            "items.*" => ['required', 'array'],
            "items.*.product_id" => ['required', 'integer', 'exists:products,id'],
            "items.*.quantity" => ['required', 'integer', 'min:1'],
        ]);
        if (!Utils::isAuthorized($request->user('admin'), "order-store")) {
            return response()->json(["error" => "Unauthorized"]);
        }
        $items = [];
        $total = 0;
        foreach ($validated["items"] as $item) {
            $product = Product::query()->findOrFail($item["product_id"]);
            $items[] = ItemResource::make(Item::query()->create([
                "user_id" => $validated["user_id"],
                "product_id" => $item["product_id"],
                "quantity" => $item["quantity"],
            ]));
            $total += $product->price * $item["quantity"] * (100 - $product->discount) / 100;
            if ($total < Config::query()->firstWhere('key', "transit-fee-max")->value) {
                $total += Config::query()->firstWhere('key', "transit-fee")->value;
            }
        }
        Order::query()->create([
            "user_id" => $validated["user_id"],
            "items" => json_encode($items),
            'timestamp' => time(),
            'status' => "completed",
            'total' => $total,
            'type' => "offline"
        ]);
        return response()->json(["message" => "Order created"]);
    }
}
