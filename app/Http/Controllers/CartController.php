<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Address;
use App\Models\Config;
use App\Models\Item;
use App\Models\Order;
use App\Models\Product;
use App\Utils\Controllers\Controller;
use App\Utils\Exceptions\InsufficientStockException;
use App\Utils\Exceptions\NonExistentAddressException;
use App\Utils\Exceptions\NonExistentItemException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @throws InsufficientStockException
     */
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
                return response()->json(["message" => "Removed item from cart"]);
            }
            if ($newQuantity > $product->stock) {
                throw new InsufficientStockException();
            }
            $item->quantity = $newQuantity;
            $item->save();
            return response()->json(["message" => "Item added to cart"]);
        }

        Item::query()->create($validated);

        return response()->json(["message" => "Item added to cart"]);
    }

    function index(Request $request): JsonResponse
    {
        return FunctionUtils::automatedPaginationWithBuilder
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

    /**
     * @throws NonExistentItemException
     */
    function destroy(Request $request, $id): JsonResponse
    {
        $item = Item::query()->where('user_id', $request->user('user')->id)->find($id);

        if (!$item) {
            throw new NonexistentItemException();
        }

        $item->delete();
        return response()->json(["message" => "Item deleted successfully"]);
    }

    /**
     * @throws NonExistentAddressException
     * @throws InsufficientStockException
     */
    function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);
        $address = Address::query()->where('user_id', $request->user('user')->id)->find($validated['address_id']);
        if (!$address) {
            throw new NonexistentAddressException();
        }
        $items = [];
        $total = 0;
        foreach ($request->user('user')->items as $item) {
            $items[] = ItemResource::make($item);
            $product = $item->product;
            var_dump($product->price, $item->quantity, $product->discount);
            $total += $product->price * $item->quantity * (100 - $product->discount) / 100;
            if ($product->stock < $item->quantity) {
                throw new InsufficientStockException();
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
