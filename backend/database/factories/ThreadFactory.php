<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject' => $this->faker->sentence(4),
            'created_by' => User::factory(),
            'last_message_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
