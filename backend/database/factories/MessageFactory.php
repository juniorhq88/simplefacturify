<?php

namespace Database\Factories;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'thread_id' => Thread::factory(),
            'sender_id' => User::factory(),
            'body' => $this->faker->paragraphs(1, true),
        ];
    }
}
