<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Info;
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

//        Info::query()->create([
//        ]);
    }
}
