<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Config;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::query()->create([
            'name' => 'admin',
            'password' => 'password',
            'is_main_admin' => true,
            'abilities' => ["*"],
            'database' => 'shopify-1',
        ]);
        User::query()->create([
            'name' => 'user',
            'password' => 'password',
            'credits' => 10000,
            'phone' => "1234567890",
        ]);
        Config::query()->create([
            'key' => 'transit-fee',
            'value' => 90,
        ]);
        Config::query()->create([
           'key' => 'transit-fee-max',
           'value' => 450,
        ]);
    }
}
