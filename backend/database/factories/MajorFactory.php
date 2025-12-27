<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Major>
 */
class MajorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faculty_id' => \App\Models\Faculty::factory(),
            'name' => fake()->randomElement([
                'Computer Science',
                'Software Engineering',
                'Electrical Engineering',
                'Mechanical Engineering',
                'Civil Engineering',
                'Mathematics',
                'Physics',
                'Chemistry',
                'Business Administration',
                'Economics',
                'Psychology',
                'English Literature',
            ]),
            'code' => fake()->unique()->regexify('[A-Z]{3}[0-9]{2}'),
            'description' => fake()->paragraph(),
            'head_of_program' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'duration_years' => fake()->numberBetween(3, 5),
            'credit_hours' => fake()->numberBetween(120, 150),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the major is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}