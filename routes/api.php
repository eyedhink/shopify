<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/user-login", [UserController::class, "login"]);
Route::post("/user-register", [UserController::class, "register"]);

Route::post("/admin-login", [AdminController::class, "login"]);

Route::middleware('auth:user')->group(function () {
});

Route::middleware('auth:admin')->group(function () {
    Route::post("/category-create", [CategoryController::class, "store"]);
    Route::get("/category-index", [CategoryController::class, "index"]);
    Route::get("/category-show/{id}", [CategoryController::class, "show"]);
    Route::put("/category-edit/{id}", [CategoryController::class, "edit"]);
    Route::delete("/category-delete/{id}", [CategoryController::class, "delete"]);
    Route::delete("/category-restore/{id}", [CategoryController::class, "restore"]);
    Route::delete("/category-force-delete/{id}", [CategoryController::class, "destroy"]);

    Route::post("/product-create", [ProductController::class, "store"]);
    Route::get("/product-index", [ProductController::class, "index"]);
    Route::get("/product-show/{id}", [ProductController::class, "show"]);
    Route::put("/product-edit/{id}", [ProductController::class, "edit"]);
    Route::delete("/product-delete/{id}", [ProductController::class, "delete"]);
    Route::delete("/product-restore/{id}", [ProductController::class, "restore"]);
    Route::delete("/product-force-delete/{id}", [ProductController::class, "destroy"]);

    Route::post("/admin-store", [AdminController::class, "store"]);
});
