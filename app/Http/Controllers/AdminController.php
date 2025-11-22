<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Services\Utils;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * @throws ValidationException
     * @throws EnvException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::query()->firstWhere('name', $validated['name']);

        if (!$admin || !Hash::check($validated['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $admin->createToken('admin-token', $request)->plainTextToken
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'admin-store')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:admins,name'],
            'password' => ['required', 'string'],
            'abilities' => ['required', 'array'],
            'abilities.*' => ['required', 'string', 'max:255'],
        ]);

        Admin::query()->create($validated);

        return response()->json(["message" => "Admin created successfully."]);
    }
}
