<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        return [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => fake()->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'name' => fake()->randomElement([
                'Introduction to Programming',
                'Data Structures and Algorithms',
                'Database Systems',
                'Web Development',
                'Machine Learning',
                'Calculus I',
                'Linear Algebra',
                'Physics I',
                'Organic Chemistry',
                'Business Ethics',
                'Marketing Principles',
                'Financial Accounting',
                'Psychology 101',
                'World History',
                'English Composition',
            ]),
            'description' => fake()->paragraphs(2, true),
            'credit_hours' => fake()->numberBetween(1, 6),
            'capacity' => fake()->numberBetween(20, 100),
            'current_enrollment' => 0,
            'semester' => fake()->randomElement(['Fall', 'Spring', 'Summer']),
            'year' => fake()->numberBetween(2024, 2026),
            'schedule' => fake()->randomElement([
                'Mon/Wed 10:00-11:30',
                'Tue/Thu 14:00-15:30',
                'Fri 09:00-12:00',
                'Mon/Wed/Fri 13:00-14:00',
            ]),
            'room' => fake()->randomElement([
                'Science Building 101',
                'Engineering Hall 205',
                'Business Center 301',
                'Arts Wing 102',
                'Library Seminar Room A',
            ]),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the course is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the course is full.
     */
    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_enrollment' => $attributes['capacity'] ?? 50,
        ]);
    }
}