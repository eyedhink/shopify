<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseResource;
use App\Imports\DataImport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $user = User::query()->firstWhere('phone', $validated["phone"]);

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
            'phone' => ['required', 'string', 'unique:users,phone'],
        ]);

        $user = User::query()->create($validated);

        return response()->json(BaseResource::make($user));
    }

    public function doesExist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
        ]);
        return response()->json(User::query()->where('phone', $validated['phone'])->exists());
    }

    function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:users,name'],
            'password' => ['required', 'string'],
        ]);
        $user = User::query()->create($validated);
        return response()->json(BaseResource::make($user));
    }

    function storeBunch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'users' => ['required', 'array'],
            'users.*.name' => ['required_with:users.*', 'string', 'unique:users,name'],
            'users.*.password' => ['required_with:users.*', 'string'],
            'users.*.phone' => ['required_with:users.*', 'string', 'unique:users,phone'],
        ]);
        foreach ($validated["users"] as $user) {
            User::query()->create($user);
        }
        return response()->json(["message" => "Users added"]);
    }

    function storeBunchExcel(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx']
        ]);
        $validated['file'] = $request->file("file")->store('excel', 'private');
        $contents = Storage::disk('local')->get($validated['file']);
        $path = Storage::disk('local')->path($validated['file']);
        var_dump($path);
       $spreadsheet = IOFactory::load($path);
       $data = $spreadsheet->getActiveSheet()->toArray();
       dd($data);
    }
}
