<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'title'     => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'type'      => 'work',        // fixa um valor simples
            'status'    => 'todo',        // idem
            'priority'  => 'medium',      // idem
            'due_date'  => now()->addDays(3),
        ];
    }
}
