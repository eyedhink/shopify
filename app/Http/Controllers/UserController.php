<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->firstWhere('name', $validated["name"]);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('user-token')->plainTextToken
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:users,name'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->create([
            'name' => $validated["name"],
            'password' => $validated["password"],
        ]);

        return response()->json(BaseResource::make($user));
    }
}
