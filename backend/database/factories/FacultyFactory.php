<?php

namespace Database\Factories;

use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faculty>
 */
class FacultyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Faculty::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $facultyNames = [
            'Faculty of Engineering',
            'Faculty of Science',
            'Faculty of Arts',
            'Faculty of Business',
            'Faculty of Medicine',
            'Faculty of Law',
            'Faculty of Education',
            'Faculty of Computer Science',
            'Faculty of Architecture',
            'Faculty of Agriculture',
        ];

        $name = $this->faker->unique()->randomElement($facultyNames);
        $code = $this->generateCode($name);

        return [
            'name' => $name,
            'code' => $code,
            'description' => $this->faker->optional()->sentence(),
            'dean_name' => $this->faker->optional()->name(),
            'email' => $this->faker->optional()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Generate a faculty code from the faculty name.
     *
     * @param string $name
     * @return string
     */
    private function generateCode(string $name): string
    {
        // Extract words and take first letters
        preg_match_all('/\b\w/', $name, $matches);
        $letters = implode('', $matches[0]);

        // If we have at least 3 letters, use first 3
        // Otherwise, generate a unique code
        if (strlen($letters) >= 3) {
            return strtoupper(substr($letters, 0, 3));
        }

        // Generate a random 3-letter code as fallback
        return strtoupper($this->faker->lexify('???'));
    }

    /**
     * Indicate that the faculty is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
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