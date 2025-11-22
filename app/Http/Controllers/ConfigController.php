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
        if (!Utils::isAuthorized($request->user('admin'), 'config-store')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate([
            'key' => ['required', 'string'],
            'value' => ['required', 'string'],
        ]);
        Config::query()->create($validated);
        return response()->json(["message" => "Config created successfully"]);
    }

    public function index(Request $request): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'config-index')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        return Utils::automatedPagination($request, Config::class, BaseResource::class);
    }

    public function show($id, Request $request): JsonResponse
    {

        if (!Utils::isAuthorized($request->user('admin'), 'config-show')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        return response()->json(BaseResource::make(Config::query()->findOrFail($id)));
    }

    public function edit(Request $request, $key): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'config-edit')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        $validated = $request->validate([
            'value' => ['required', 'string'],
        ]);
        Config::query()->firstWhere("key", $key)->update($validated);
        return response()->json(["message" => "Config updated successfully"]);
    }

    public function destroy($id, Request $request): JsonResponse
    {
        if (!Utils::isAuthorized($request->user('admin'), 'config-destroy')) {
            return response()->json(["error" => "Unauthorized."]);
        }
        Config::query()->findOrFail($id)->delete();
        return response()->json(["message" => "Config deleted successfully"]);
    }
}
