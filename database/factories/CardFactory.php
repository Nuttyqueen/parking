<?php

namespace Database\Factories;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{

    protected $model = Card::class;

    public function definition()
    {
        $randomLocation = ['Building A', 'Building B'];

        return [
            'name' => $this->faker->name(),
            'location' => $this->faker->randomElement($randomLocation),
        ];
    }
}
