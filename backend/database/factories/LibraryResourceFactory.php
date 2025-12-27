<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LibraryResource>
 */
class LibraryResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => null,
            'faculty_id' => null,
            'created_by' => \App\Models\User::factory(),
            'title' => fake()->randomElement([
                'Introduction to Algorithms',
                'Data Structures and Algorithms',
                'Machine Learning Fundamentals',
                'Web Development Complete Guide',
                'Database Management Systems',
                'Computer Networks',
                'Operating Systems',
                'Software Engineering Principles',
                'Artificial Intelligence',
                'Cloud Computing Concepts',
            ]),
            'description' => fake()->paragraphs(2, true),
            'resource_type' => fake()->randomElement([
                'document',
                'video',
                'link',
                'book',
                'article',
                'journal',
                'other',
            ]),
            'access_level' => fake()->randomElement(['public', 'students', 'faculty', 'specific_course', 'specific_faculty']),
            'file_url' => fake()->optional(50)->url(),
            'file_type' => fake()->optional()->randomElement(['pdf', 'doc', 'docx', 'mp4', 'mp3', 'zip']),
            'file_size' => fake()->optional()->numberBetween(1024, 104857600), // 1KB to 100MB
            'external_link' => fake()->optional(30)->url(),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'isbn' => fake()->optional(60)->isbn13(),
            'doi' => fake()->optional(40)->regexify('10\.\d{4,9}/[^\s]+'),
            'publication_year' => fake()->numberBetween(2000, 2024),
            'tags' => fake()->optional()->randomElement([
                'programming,algorithms,computer science',
                'web development,html,css,javascript',
                'machine learning,ai,data science',
                'database,sql,nosql',
                'networking,protocols,security',
                'software engineering,agile,devops',
            ]),
            'download_count' => fake()->numberBetween(0, 1000),
            'view_count' => fake()->numberBetween(0, 5000),
            'is_published' => fake()->boolean(80), // 80% chance of being published
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the resource is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the resource is not published (draft).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the resource is a book.
     */
    public function book(): static
    {
        return $this->state(fn (array $attributes) => [
            'resource_type' => 'book',
            'isbn' => fake()->isbn13(),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
        ]);
    }

    /**
     * Indicate that the resource is a document.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'resource_type' => 'document',
            'file_url' => fake()->url(),
            'file_type' => 'pdf',
            'file_size' => fake()->numberBetween(1024, 10485760),
        ]);
    }

    /**
     * Indicate that the resource is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'resource_type' => 'video',
            'file_url' => fake()->url(),
            'file_type' => 'mp4',
            'file_size' => fake()->numberBetween(10485760, 524288000),
        ]);
    }

    /**
     * Indicate that the resource is an external link.
     */
    public function externalLink(): static
    {
        return $this->state(fn (array $attributes) => [
            'resource_type' => 'link',
            'external_link' => fake()->url(),
            'file_url' => null,
        ]);
    }

    /**
     * Indicate that the resource has public access.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => 'public',
        ]);
    }

    /**
     * Indicate that the resource has faculty-only access.
     */
    public function facultyOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => 'faculty',
        ]);
    }

    /**
     * Indicate that the resource is for a specific course.
     */
    public function forCourse(int $courseId): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $courseId,
            'access_level' => 'specific_course',
        ]);
    }

    /**
     * Indicate that the resource is for a specific faculty.
     */
    public function forFaculty(int $facultyId): static
    {
        return $this->state(fn (array $attributes) => [
            'faculty_id' => $facultyId,
        ]);
    }
}