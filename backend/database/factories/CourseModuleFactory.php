<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseModule>
 */
class CourseModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => \App\Models\Course::factory(),
            'title' => fake()->randomElement([
                'Module 1: Introduction',
                'Module 2: Fundamentals',
                'Module 3: Advanced Concepts',
                'Module 4: Practical Applications',
                'Module 5: Case Studies',
                'Getting Started',
                'Core Concepts',
                'Building Blocks',
                'Advanced Topics',
                'Final Project',
            ]),
            'description' => fake()->paragraphs(2, true),
            'content' => fake()->paragraphs(3, true),
            'video_url' => fake()->optional(50)->url(),
            'document_url' => fake()->optional(50)->url(),
            'order' => fake()->numberBetween(0, 100),
            'is_published' => fake()->boolean(80), // 80% chance of being published
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the module is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the module is not published (draft).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the module is for a specific course.
     */
    public function forCourse(int $courseId): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $courseId,
        ]);
    }
}