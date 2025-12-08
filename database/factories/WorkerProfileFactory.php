<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkerProfile>
 */
class WorkerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->worker(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'birthdate' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'skills' => fake()->optional()->text(200),
            'experiences' => fake()->optional()->text(200),
            'desired_jobs' => fake()->optional()->text(100),
            'desired_location_id' => fake()->optional()->passthrough(\App\Models\Location::factory()),
        ];
    }
}
