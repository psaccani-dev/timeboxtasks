<?php

namespace Database\Factories;

use App\Models\TimeBox;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeBoxFactory extends Factory
{
    protected $model = TimeBox::class;

    public function definition(): array
    {
        $start = now()->addHours($this->faker->numberBetween(1, 24));
        $end   = (clone $start)->addHour();

        return [
            'user_id' => User::factory(),
            'title'   => 'Focus Block',
            'type'    => 'focus',
            'start_at' => $start,
            'end_at'   => $end,
        ];
    }
}
