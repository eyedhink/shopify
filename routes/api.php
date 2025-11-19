<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/user-login", [UserController::class, "login"]);
Route::post("/user-register", [UserController::class, "register"]);
Route::get("/does-exist", [UserController::class, "doesExist"]);

Route::post("/admin-login", [AdminController::class, "login"]);

Route::middleware('auth:user')->group(function () {
    Route::post("/cart-store", [CartController::class, "store"]);
    Route::get("/cart-index", [CartController::class, "index"]);
    Route::get("/cart-show/{id}", [CartController::class, "show"]);
    Route::get("/cart-delete/{id}", [CartController::class, "destroy"]);
    Route::post("/cart-submit", [CartController::class, "submit"]);
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

    Route::post("/user-store", [UserController::class, "store"]);
    Route::post("/user-store-bunch", [UserController::class, "storeBunch"]);
    Route::post("/user-store-bunch-excel", [UserController::class, "storeBunchExcel"]);

    Route::get("/order-index", [OrderController::class, "index"]);
    Route::get("/order-show/{id}", [OrderController::class, "show"]);
    Route::put("/order-update-status/{id}", [OrderController::class, "updateStatus"]);
    Route::delete("/order-delete/{id}", [OrderController::class, "delete"]);
    Route::delete("/order-restore/{id}", [OrderController::class, "restore"]);
    Route::delete("/order-force-delete/{id}", [OrderController::class, "destroy"]);
});
