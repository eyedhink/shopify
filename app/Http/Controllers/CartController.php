<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Config;
use App\Models\Item;
use App\Models\Order;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'decimal', 'not_in:0'],
        ]);

        $item = Item::query()->where('user_id', $validated['user_id'])->firstWhere('product_id', $validated['product_id']);

        if ($item !== null) {
            $newQuantity = $item->quantity + $validated['quantity'];
            if ($newQuantity <= 0) {
                $item->delete();
                return response()->json(["error" => "Removed item from cart"]);
            }
            if ($newQuantity > $item->stock) {
                return response()->json(["error" => "Insufficient stock"], 400);
            }
            $item->quantity = $newQuantity;
            $item->save();
            return response()->json(["message" => "Item added to cart"]);
        }

        if ($validated["quantity"] <= 0) {
            return response()->json(["error" => "invalid quantity"]);
        }

        Item::query()->create($validated);

        return response()->json(["message" => "Item added to cart"]);
    }

    function index(Request $request): JsonResponse
    {
        return Utils::automatedPaginationWithBuilder($request, Item::with(["user", "product"]), ItemResource::class);
    }

    function show($id): JsonResponse
    {
        return response()->json(ItemResource::make(Item::query()->findOrFail($id)));
    }

    function destroy($id): JsonResponse
    {
        Item::query()->findOrFail($id)->delete();
        return response()->json(["message" => "Item deleted successfully"]);
    }

    function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string'],
        ]);
        $items = [];
        $total = 0;
        foreach ($request->user('user')->items as $item) {
            $items[] = ItemResource::make($item);
            $total += $item->quantity * $item->price;
            $item->delete();
        }
        if($total < Config::query()->firstWhere('key' , "transit-fee-max")->value) {
            $total += Config::query()->firstWhere('key' , "transit-fee")->value;
        }
        Order::query()->create([
            'user_id' => $request->user('user')->id,
            'items' => json_encode($items),
            'timestamp' => time(),
            'address' => $validated['address'],
            'total' => $total,
        ]);
        return response()->json(["message" => "Order submitted"]);
    }
}
