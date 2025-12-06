<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Utils\Controllers\Controller;
use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Exceptions\AlreadyPaidException;
use App\Utils\Exceptions\NonExistentOrderException;
use App\Utils\Exceptions\YouArePoorException;
use App\Utils\Functions\FunctionUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return FunctionUtils::automatedPaginationWithBuilder
        (
            $request,
            Order::with(["user", "address"])
                ->where("user_id", $request->user('user')->id),
            OrderResource::class
        );
    }

    public function show(Request $request, $id): JsonResponse
    {
        return response()->json(
            OrderResource::make
            (
                Order::with(["user", "address"])
                    ->where('user_id', $request->user('user')->id)
                    ->findOrFail($id)
            )
        );
    }

    /**
     * @throws AccessDeniedException
     * @throws NonExistentOrderException
     * @throws AlreadyPaidException
     * @throws YouArePoorException
     */
    public function pay(Request $request, $id): JsonResponse
    {
        $user = User::query()->findOrFail($request->user('user')->id);
        $order = Order::query()->findOrFail($id);
        if (!$order) {
            throw new NonExistentOrderException();
        }
        if ($order->user_id != $user->id) {
            throw new AccessDeniedException();
        }
        if ($order->status != "pending payment") {
            throw new AlreadyPaidException();
        }
        if ($user->credits < $order->total) {
            throw new YouArePoorException();
        }
        $user->credits -= $order->total;
        $user->save();
        $order->status = "pending confirmation";
        $order->save();
        return response()->json(["message" => "Payment successful"]);
    }
}
