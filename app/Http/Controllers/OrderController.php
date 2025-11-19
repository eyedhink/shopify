<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Item;
use App\Models\Order;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return Utils::automatedPaginationWithBuilder($request, Order::with(["user"]), OrderResource::class);
    }

    public function show($id): JsonResponse
    {
        return response()->json(OrderResource::make(Order::with(["user"])->findOrFail($id)));
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        $validated = $request->validate([
            "status" => ['required', 'string', 'in:processing,sending,completed'],
        ]);
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

    public function delete($id): JsonResponse
    {
        Order::query()->findOrFail($id)->delete();
        return response()->json(["message" => "Order deleted"]);
    }

    public function restore($id): JsonResponse
    {
        $order = Order::withTrashed()->findOrFail($id);
        $order->restore();
        $order->status = "pending payment";
        return response()->json(["message" => "Order restored"]);
    }

    public function destroy($id): JsonResponse
    {
        Order::query()->findOrFail($id)->forceDelete();
        return response()->json(["message" => "Order deleted"]);
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
        $items = [];
        $total = 0;
        foreach ($validated["items"] as $item) {
            $items[] = ItemResource::make(Item::query()->create([
                "user_id" => $validated["user_id"],
                "product_id" => $item["product_id"],
                "quantity" => $item["quantity"],
            ]));
            $total += $item["quantity"] * $item["price"];
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
