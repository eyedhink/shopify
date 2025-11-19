<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseResource;
use App\Models\Config;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string'],
            'value' => ['required', 'string'],
        ]);
        Config::query()->create($validated);
        return response()->json(["message" => "Config created successfully"]);
    }

    public function index(Request $request): JsonResponse
    {
        return Utils::automatedPagination($request, Config::class, BaseResource::class);
    }

    public function show($id): JsonResponse
    {
        return response()->json(BaseResource::make(Config::query()->findOrFail($id)));
    }

    public function edit(Request $request, $key): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'string'],
        ]);
        Config::query()->firstWhere("key", $key)->update($validated);
        return response()->json(["message" => "Config updated successfully"]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'string'],
        ]);
        Config::query()->findOrFail($id)->update($validated);
        return response()->json(["message" => "Config updated successfully"]);
    }

    public function destroy($id): JsonResponse
    {
        Config::query()->findOrFail($id)->delete();
        return response()->json(["message" => "Config deleted successfully"]);
    }
}
