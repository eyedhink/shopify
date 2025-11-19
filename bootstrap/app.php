<?php

use App\Models\Order;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(function () {
            $orders = Order::query()->where('status', "pending payment")->get();
            if ($orders !== null) {
                foreach ($orders as $order) {
                    if (time() - Carbon::parse($order->created_at)->timestamp < 1800) {
                        $order->status = "canceled";
                        $order->save();
                        $order->delete();
                    }
                }
            }
        })->everyFiveSeconds();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
