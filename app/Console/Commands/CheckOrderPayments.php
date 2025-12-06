<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckOrderPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-order-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check if orders haven't been paid for a long time";

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
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
    }
}
