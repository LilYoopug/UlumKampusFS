<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faculty>
 */
class FacultyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Faculty of Engineering',
                'Faculty of Science',
                'Faculty of Arts',
                'Faculty of Business',
                'Faculty of Medicine',
                'Faculty of Law',
                'Faculty of Computer Science',
                'Faculty of Architecture',
            ]),
            'code' => fake()->unique()->regexify('[A-Z]{2,4}'),
            'description' => fake()->paragraph(),
            'dean_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the faculty is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}