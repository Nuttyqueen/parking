<?php

namespace Database\Seeders;
use App\Models\Card;
use App\Models\ParkingSlot;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        Card::factory(10)->create();
        /* ParkingSlot::factory(5)->create(); */
    }
}
