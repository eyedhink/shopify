<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Address;
use App\Models\Config;
use App\Models\Item;
use App\Models\Order;
use App\Models\Product;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'not_in:0'],
        ]);

        $validated['user_id'] = $request->user('user')->id;

        $product = Product::query()->findOrFail($validated['product_id']);

        $item = Item::query()->where('user_id', $validated['user_id'])->firstWhere('product_id', $validated['product_id']);

        if ($item !== null) {
            $newQuantity = $item->quantity + $validated['quantity'];
            if ($newQuantity <= 0) {
                $item->delete();
                return response()->json(["error" => "Removed item from cart"]);
            }
            if ($newQuantity > $product->stock) {
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
        return Utils::automatedPaginationWithBuilder
        (
            $request,
            Item::with(["user", "product"])
                ->where(
                    'user_id', $request
                    ->user('user')
                    ->id
                ),
            ItemResource::class
        );
    }

    function show(Request $request, $id): JsonResponse
    {
        return response()->json(
            ItemResource::make
            (
                Item::with(["user", "product"])
                    ->where('user_id', $request->user('user')->id)
                    ->findOrFail($id)
            )
        );
    }

    function destroy(Request $request, $id): JsonResponse
    {
        $item = Item::query()->findOrFail($id);

        if ($item->user_id != $request->user('user')->id) {
            return response()->json(['error' => 'Not allowed to delete item']);
        }

        $item->delete();
        return response()->json(["message" => "Item deleted successfully"]);
    }

    function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);
        $address = Address::query()->findOrFail($validated['address_id']);
        if ($address->user_id != $request->user('user')->id) {
            return response()->json(['error' => 'Not allowed to use address']);
        }
        $items = [];
        $total = 0;
        foreach ($request->user('user')->items as $item) {
            $items[] = ItemResource::make($item);
            $product = $item->product;
            var_dump($product->price, $item->quantity, $product->discount);
            $total += $product->price * $item->quantity * (100 - $product->discount) / 100;
            if ($product->stock < $item->quantity) {
                return response()->json(["error" => "Insufficient stock"]);
            }
            $product->stock -= $item->quantity;
            $product->save();
        }
        foreach ($request->user('user')->items as $item) {
            $item->delete();
        }
        if ($total < Config::query()->firstWhere('key', "transit-fee-max")->value) {
            $total += Config::query()->firstWhere('key', "transit-fee")->value;
        }
        Order::query()->create([
            'user_id' => $request->user('user')->id,
            'items' => json_encode($items),
            'timestamp' => time(),
            'address_id' => $validated['address_id'],
            'total' => $total,
        ]);
        return response()->json(["message" => "Order submitted"]);
    }
}
