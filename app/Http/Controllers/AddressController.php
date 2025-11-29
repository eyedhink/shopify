<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Utils\Controllers\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Address::class,
            resource: AddressResource::class,
            validation: [
                'name' => ['required', 'string'],
                'address' => ['required', 'string'],
            ],
            validation_extensions: [
                'store' => [
                    'user_id' => fn(Request $request, array $validated) => $request->user('user')->id,
                ]
            ],
            selection_query: fn(Request $request): Builder => Address::with(['user'])->where('user_id', $request->user('user')->id),
        );
    }
}
