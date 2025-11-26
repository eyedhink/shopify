<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
        ]);
        $validated['user_id'] = $request->user('user')->id;
        Address::query()->create($validated);
        return response()->json(["message" => "Address Created"]);
    }

    public function index(Request $request): JsonResponse
    {
        return Utils::automatedPaginationWithBuilder
        (
            $request,
            Address::with('user')
                ->where('user_id', $request->user('user')->id),
            AddressResource::class
        );
    }

    public function show(Request $request, $id): JsonResponse
    {
        return response()->json
        (
            AddressResource::make
            (
                Address::with('user')
                    ->where('user_id', $request->user('user')->id)
                    ->findOrFail($id)
            )
        );
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string'],
        ]);

        $address = Address::query()->findOrFail($id);

        if ($request->user('user')->id != $address->user_id) {
            return response()->json(["message" => "You can't edit this address"]);
        }

        $address->update($validated);

        return response()->json(["message" => "Address Edited"]);
    }

    public function delete(Request $request, $id): JsonResponse
    {
        $address = Address::query()->findOrFail($id);
        if ($address->user_id != $request->user('user')->id) {
            return response()->json(["message" => "You can't delete this address"]);
        }
        $address->delete();
        return response()->json(["message" => "Address Deleted"]);
    }
}
