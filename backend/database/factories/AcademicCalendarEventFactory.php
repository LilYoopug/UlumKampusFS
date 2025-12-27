<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AcademicCalendarEvent;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicCalendarEvent>
 */
class AcademicCalendarEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AcademicCalendarEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+6 months');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 30) . ' days');

        return [
            'title' => $this->faker->randomElement([
                'Midterm Exams',
                'Final Exams',
                'Spring Break',
                'Winter Break',
                'Course Registration',
                'Graduation Ceremony',
                'Faculty Meeting',
                'Student Orientation',
                'Parent-Teacher Conference',
                'Academic Conference',
            ]),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'category' => $this->faker->randomElement([
                'exam',
                'holiday',
                'registration',
                'orientation',
                'graduation',
                'conference',
                'workshop',
                'other',
            ]),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}