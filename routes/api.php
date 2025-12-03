<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageControllerAdmin;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketControllerAdmin;
use App\Http\Controllers\UserController;
use App\Utils\Middleware\AuthorizeAbility;
use Illuminate\Support\Facades\Route;

Route::post("/admin-login", [AdminController::class, "login"]);

Route::get("/category-index", [CategoryController::class, "index"]);
Route::get("/category-show/{kw}", [CategoryController::class, "show"]);

Route::get("/product-index", [ProductController::class, "index"]);
Route::get("/product-show/{kw}", [ProductController::class, "show"]);

Route::post("/user-login", [UserController::class, "login"]);
Route::post("/user-register", [UserController::class, "register"]);
Route::get("/user-does-exist", [UserController::class, "doesExist"]);

Route::middleware('auth:user')->group(function () {
    Route::post("/address-store", [AddressController::class, "store"]);
    Route::get("/address-index", [AddressController::class, "index"]);
    Route::get("/address-show/{kw}", [AddressController::class, "show"]);
    Route::put("/address-edit/{kw}", [AddressController::class, "edit"]);
    Route::delete("/address-delete/{kw}", [AddressController::class, "destroy"]);

    Route::post("/cart-store", [CartController::class, "store"]);
    Route::get("/cart-index", [CartController::class, "index"]);
    Route::get("/cart-show/{id}", [CartController::class, "show"]);
    Route::delete("/cart-delete/{id}", [CartController::class, "destroy"]);
    Route::post("/cart-submit", [CartController::class, "submit"]);

    Route::post("/message-store-user", [MessageController::class, "store"]);
    Route::get("/message-index-user", [MessageController::class, "index"]);
    Route::get("/message-show-user/{kw}", [MessageController::class, "show"]);
    Route::put("/message-edit-user/{kw}", [MessageController::class, "edit"]);
    Route::delete("/message-destroy-user/{kw}", [MessageController::class, "destroy"]);

    Route::get("/order-index-user", [OrderController::class, "index"]);
    Route::get("/order-show-user/{id}", [OrderController::class, "show"]);
    Route::post("/order-pay/{id}", [OrderController::class, "pay"]);
    Route::delete("/order-force-delete-user/{id}", [OrderController::class, "destroy"]);

    Route::post("/ticket-store", [TicketController::class, "store"]);
    Route::get("/ticket-index", [TicketController::class, "index"]);
    Route::get("/ticket-show/{kw}", [TicketController::class, "show"]);
    Route::put("/ticket-edit/{kw}", [TicketController::class, "edit"]);
    Route::delete("/ticket-delete/{kw}", [TicketController::class, "delete"]);
    Route::delete("/ticket-restore/{kw}", [TicketController::class, "restore"]);
});

Route::middleware('auth:admin')->group(function () {
    Route::post("/admin-store", [AdminController::class, "store"])
        ->middleware(AuthorizeAbility::class . ':admin-store');

    Route::post("/category-create", [CategoryController::class, "store"])
        ->middleware(AuthorizeAbility::class . ':category-store');
    Route::put("/category-edit/{kw}", [CategoryController::class, "edit"])
        ->middleware(AuthorizeAbility::class . ':category-edit');
    Route::delete("/category-delete/{kw}", [CategoryController::class, "delete"])
        ->middleware(AuthorizeAbility::class . ':category-delete');
    Route::delete("/category-restore/{kw}", [CategoryController::class, "restore"])
        ->middleware(AuthorizeAbility::class . ':category-restore');

    Route::post("/config-store", [ConfigController::class, "store"])
        ->middleware(AuthorizeAbility::class . ':config-store');
    Route::get("/config-index", [ConfigController::class, "index"])
        ->middleware(AuthorizeAbility::class . ':config-index');
    Route::get("/config-show/{kw}", [ConfigController::class, "show"])
        ->middleware(AuthorizeAbility::class . ':config-show');
    Route::put("/config-edit/{kw}", [ConfigController::class, "edit"])
        ->middleware(AuthorizeAbility::class . ':config-edit');
    Route::delete("/config-delete/{kw}", [ConfigController::class, "destroy"])
        ->middleware(AuthorizeAbility::class . ':config-destroy');

    Route::post("/message-store-admin", [MessageControllerAdmin::class, "store"])
        ->middleware(AuthorizeAbility::class . ':message-store');
    Route::get("/message-index-admin", [MessageControllerAdmin::class, "index"])
        ->middleware(AuthorizeAbility::class . ':message-index');
    Route::get("/message-show-admin/{kw}", [MessageControllerAdmin::class, "show"])
        ->middleware(AuthorizeAbility::class . ':message-show');
    Route::put("/message-edit-admin/{kw}", [MessageControllerAdmin::class, "edit"])
        ->middleware(AuthorizeAbility::class . ':message-edit');
    Route::delete("/message-destroy-admin/{kw}", [MessageControllerAdmin::class, "destroy"])
        ->middleware(AuthorizeAbility::class . ':message-destroy');

    Route::get("/order-index", [OrderController::class, "indexAdmin"])
        ->middleware(AuthorizeAbility::class . ':order-index');
    Route::get("/order-show/{id}", [OrderController::class, "showAdmin"])
        ->middleware(AuthorizeAbility::class . ':order-show');
    Route::put("/order-update-status/{id}", [OrderController::class, "updateStatus"])
        ->middleware(AuthorizeAbility::class . ':order-update-status');
    Route::delete("/order-delete/{id}", [OrderController::class, "delete"])
        ->middleware(AuthorizeAbility::class . ':order-delete');
    Route::delete("/order-restore/{id}", [OrderController::class, "restore"])
        ->middleware(AuthorizeAbility::class . ':order-restore');
    Route::post("/order-manual-store", [OrderController::class, "store"])
        ->middleware(AuthorizeAbility::class . ':order-manual-store');

    Route::post("/product-create", [ProductController::class, "store"])
        ->middleware(AuthorizeAbility::class . ':product-store');
    Route::post("/product-edit/{kw}", [ProductController::class, "edit"])
        ->middleware(AuthorizeAbility::class . ':product-edit');
    Route::delete("/product-delete/{kw}", [ProductController::class, "delete"])
        ->middleware(AuthorizeAbility::class . ':product-delete');
    Route::delete("/product-restore/{kw}", [ProductController::class, "restore"])
        ->middleware(AuthorizeAbility::class . ':product-restore');

    Route::get("/ticket-index-admin", [TicketControllerAdmin::class, "index"])
        ->middleware(AuthorizeAbility::class . ':ticket-index');
    Route::get("/ticket-show-admin/{kw}", [TicketControllerAdmin::class, "show"])
        ->middleware(AuthorizeAbility::class . ':ticket-show');
    Route::put("/ticket-edit-admin/{kw}", [TicketControllerAdmin::class, "edit"])
        ->middleware(AuthorizeAbility::class . ':ticket-edit');
    Route::delete("/ticket-delete-admin/{kw}", [TicketControllerAdmin::class, "delete"])
        ->middleware(AuthorizeAbility::class . ':ticket-delete');
    Route::delete("/ticket-restore-admin/{kw}", [TicketControllerAdmin::class, "restore"])
        ->middleware(AuthorizeAbility::class . ':ticket-restore');

    Route::post("/user-store", [UserController::class, "store"])
        ->middleware(AuthorizeAbility::class . ':user-store');
    Route::post("/user-store-bunch", [UserController::class, "storeBunch"])
        ->middleware(AuthorizeAbility::class . ':user-store-bunch');
    Route::post("/user-store-bunch-excel", [UserController::class, "storeBunchExcel"])
        ->middleware(AuthorizeAbility::class . ':user-store-bunch-excel');
});
