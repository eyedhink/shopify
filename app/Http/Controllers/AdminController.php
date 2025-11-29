<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Utils\Controllers\Controller;
use App\Utils\Functions\FunctionUtils;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PDO;

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

        $editor = new EnvEditor(new Repository(), new Filesystem());
        $pdo = new PDO("sqlite:D:\programming\PHP\shopify\database\db.sqlite");
        $stmt = $pdo->query("SELECT name FROM `databases`");
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $editor->editKey('DB_DATABASE', $name);

            $admin = Admin::query()->firstWhere('name', $validated['name']);

            if (!$admin) {
                continue;
            }

            if (!Hash::check($validated['password'], $admin->password)) {
                continue;
            }

            return response()->json([
                'token' => $admin->createToken('admin-token', $request)->plainTextToken
            ]);
        }
        throw ValidationException::withMessages([
            'name' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!FunctionUtils::isAuthorized($request->user('admin'), 'admin-store')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:admins,name'],
            'password' => ['required', 'string'],
            'abilities' => ['required', 'array'],
            'abilities.*' => ['required', 'string', 'max:255'],
        ]);

        $editor = new EnvEditor(new Repository(), new Filesystem());

        $validated['database'] = $editor->getKey('DB_DATABASE');

        Admin::query()->create($validated);

        return response()->json(["message" => "Admin created successfully."]);
    }
}
