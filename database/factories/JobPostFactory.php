<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPost>
 */
class JobPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => User::factory()->company(),
            'title' => fake()->jobTitle(),
            'description' => fake()->realText(200),
            'employment_type_id' => fake()->numberBetween(1, 4), // 1=正社員、2=契約社員、3=パート、4=アルバイト
            'work_style_id' => fake()->numberBetween(1, 4), // 1=出社、2=リモート、3=週3日、4=時短
            'industry_id' => fake()->numberBetween(1, 4), // 1=飲食、2=製造、3=システム開発、4=教育
            'location_id' => Location::factory(),
            'working_hours' => fake()->randomElement(['9:00-18:00', '10:00-19:00', '8:00-17:00']),
            'salary' => fake()->numberBetween(200000, 600000),
            'number_of_positions' => fake()->numberBetween(1, 5),
            'posted_at' => now(),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+3 months'),
        ];
    }
}
