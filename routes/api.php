<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/user-login", [UserController::class, "login"]);
Route::post("/user-register", [UserController::class, "register"]);
Route::get("/does-exist", [UserController::class, "doesExist"]);

Route::post("/admin-login", [AdminController::class, "login"]);

Route::get("/category-index", [CategoryController::class, "index"]);
Route::get("/category-show/{id}", [CategoryController::class, "show"]);

Route::get("/product-index", [ProductController::class, "index"]);
Route::get("/product-show/{id}", [ProductController::class, "show"]);

Route::middleware('auth:user')->group(function () {
    Route::post("/cart-store", [CartController::class, "store"]);
    Route::get("/cart-index", [CartController::class, "index"]);
    Route::get("/cart-show/{id}", [CartController::class, "show"]);
    Route::get("/cart-delete/{id}", [CartController::class, "destroy"]);
    Route::post("/cart-submit", [CartController::class, "submit"]);

    Route::post("/address-store", [AddressController::class, "store"]);
    Route::get("/address-index", [AddressController::class, "index"]);
    Route::get("/address-show/{id}", [AddressController::class, "show"]);
    Route::put("/address-edit/{id}", [AddressController::class, "edit"]);
    Route::delete("/address-delete/{id}", [AddressController::class, "delete"]);

    Route::post("/message-store-user", [MessageController::class, "store"]);
    Route::get("/message-index-user", [MessageController::class, "index"]);
    Route::get("/message-show-user", [MessageController::class, "show"]);
    Route::put("/message-edit-user", [MessageController::class, "edit"]);
    Route::delete("/message-destroy-user", [MessageController::class, "destroy"]);

    Route::get("/order-index-user", [OrderController::class, "index"]);
    Route::get("/order-show-user/{id}", [OrderController::class, "show"]);
    Route::post("/order-pay", [OrderController::class, "pay"]);
    Route::delete("/order-force-delete-user/{id}", [OrderController::class, "destroy"]);

    Route::post("/ticket-store", [TicketController::class, "store"]);
    Route::get("/ticket-index", [TicketController::class, "index"]);
    Route::get("/ticket-show/{id}", [TicketController::class, "show"]);
    Route::put("/ticket-edit/{id}", [TicketController::class, "edit"]);
    Route::delete("/ticket-delete/{id}", [TicketController::class, "delete"]);
    Route::delete("/ticket-restore/{id}", [TicketController::class, "restore"]);
    Route::delete("/ticket-force-delete/{id}", [TicketController::class, "destroy"]);
});

Route::middleware('auth:admin')->group(function () {
    Route::post("/category-create", [CategoryController::class, "store"]);
    Route::put("/category-edit/{id}", [CategoryController::class, "edit"]);
    Route::delete("/category-delete/{id}", [CategoryController::class, "delete"]);
    Route::delete("/category-restore/{id}", [CategoryController::class, "restore"]);
    Route::delete("/category-force-delete/{id}", [CategoryController::class, "destroy"]);

    Route::post("/product-create", [ProductController::class, "store"]);
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
    Route::post("/order-manual-store", [OrderController::class, "store"]);

    Route::post("/config-store", [ConfigController::class, "store"]);
    Route::get("/config-index", [ConfigController::class, "index"]);
    Route::get("/config-show/{id}", [ConfigController::class, "show"]);
    Route::put("/config-edit/{id}", [ConfigController::class, "edit"]);
    Route::delete("/config-delete/{id}", [ConfigController::class, "destroy"]);

    Route::post("/message-store-admin", [MessageController::class, "store"]);
    Route::get("/message-index-admin", [MessageController::class, "index"]);
    Route::get("/message-show-admin", [MessageController::class, "show"]);
    Route::put("/message-edit-admin", [MessageController::class, "edit"]);
    Route::delete("/message-destroy-admin", [MessageController::class, "destroy"]);

    Route::get("/ticket-index-admin", [TicketController::class, "index"]);
    Route::get("/ticket-show-admin/{id}", [TicketController::class, "show"]);
    Route::put("/ticket-edit-admin/{id}", [TicketController::class, "edit"]);
    Route::delete("/ticket-delete-admin/{id}", [TicketController::class, "delete"]);
    Route::delete("/ticket-restore-admin/{id}", [TicketController::class, "restore"]);
    Route::delete("/ticket-force-delete-admin/{id}", [TicketController::class, "destroy"]);
});
