<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicCalendarEvent>
 */
class AcademicCalendarEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 day', '+90 days');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 30).' days');

        return [
            'title' => fake()->randomElement([
                'Final Exams',
                'Course Registration',
                'Spring Semester Begins',
                'Fall Semester Begins',
                'Holiday Break',
                'Orientation Week',
                'Graduation Ceremony',
                'Midterm Exams',
                'Course Drop Deadline',
                'Library Closed',
                'Student Assembly',
            ]),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'category' => fake()->randomElement(array_keys(\App\Models\AcademicCalendarEvent::getCategories())),
            'description' => fake()->optional()->text(),
        ];
    }
}