<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Imports\UserImport;
use App\Models\User;
use App\Utils\Controllers\Controller;
use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Exceptions\InvalidCredentialsException;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PDO;
use Maatwebsite\Excel\Facades\Excel;
use function PHPUnit\Framework\isNull;

class UserController extends Controller
{
    /**
     * @throws EnvException
     * @throws InvalidCredentialsException|AccessDeniedException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $editor = new EnvEditor(new Repository(), new Filesystem());
        $pdo = new PDO("sqlite:D:\programming\PHP\shopify\database\db.sqlite");
        $stmt = $pdo->query("SELECT name FROM `databases`");
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $editor->editKey('DB_DATABASE', $name);
            $user = User::query()->firstWhere('phone', $validated['phone']);

            if (!$user) {
                continue;
            }

            if (!Hash::check($validated['password'], $user->password)) {
                continue;
            }

            return response()->json([
                'token' => $user->createToken('user-token', $request)->plainTextToken
            ]);
        }
        throw new InvalidCredentialsException();
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:users,name'],
            'password' => ['required', 'string'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'database' => ['required', 'string'],
        ]);

        $user = User::query()->create($validated);

        return response()->json(UserResource::make($user));
    }

    public function doesExist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
        ]);
        $user = User::query()->firstWhere('phone', $validated['phone']);
        return response()->json(isNull($user));
    }

    function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:users,name'],
            'password' => ['required', 'string'],
            'phone' => ['required', 'string', 'unique:users,phone'],
        ]);

        $editor = new EnvEditor(new Repository(), new Filesystem());

        $validated['database'] = $editor->getKey('DB_DATABASE');

        $user = User::query()->create($validated);
        return response()->json(UserResource::make($user));
    }

    function storeBunch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'users' => ['required', 'array'],
            'users.*.name' => ['required_with:users.*', 'string', 'unique:users,name'],
            'users.*.password' => ['required_with:users.*', 'string'],
            'users.*.phone' => ['required_with:users.*', 'string', 'unique:users,phone'],
        ]);
        $editor = new EnvEditor(new Repository(), new Filesystem());
        $database = $editor->getKey('DB_DATABASE');
        foreach ($validated["users"] as $user) {
            $user['database'] = $database;
            User::query()->create($user);
        }
        return response()->json(["message" => "Users added"]);
    }

    function storeBunchExcel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx']
        ]);
        $validated['file'] = $request->file("file")->store('excel', 'private');
        $path = Storage::disk('local')->path($validated['file']);
        Excel::import(new UserImport(), $path);
        return response()->json(["message" => "NICE!"]);
    }
}
