<?php

namespace Database\Factories;
use App\Models\ParkingSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParkingSlotFactory extends Factory
{
    protected $model = ParkingSlot::class;

    public function definition()
    {

        return [
            'slot_code' => 'D',
            'parking_lot_id' => '2',
            'is_available' => '0',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (ParkingSlot $slot) {
            $slot->slot_number = $this->faker->unique()->numberBetween(1, 5);
            $slot->save();
        });
    }
}
