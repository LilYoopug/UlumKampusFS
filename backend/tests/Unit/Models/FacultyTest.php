<?php

namespace Tests\Unit\Models;

use App\Models\Faculty;
use App\Models\Major;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_has_majors_relationship()
    {
        $faculty = Faculty::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Faculty of Engineering',
        ]);

        $major1 = Major::create([
            'faculty_id' => $faculty->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $major2 = Major::create([
            'faculty_id' => $faculty->id,
            'name' => 'Software Engineering',
            'code' => 'SE',
        ]);

        $majors = $faculty->majors;

        $this->assertCount(2, $majors);
        $this->assertInstanceOf(Major::class, $majors->first());
        $this->assertEquals($major1->id, $majors->first()->id);
        $this->assertEquals($major2->id, $majors->last()->id);
    }

    public function test_faculty_returns_empty_majors_when_none_exist()
    {
        $faculty = Faculty::create([
            'name' => 'Arts',
            'code' => 'ART',
            'description' => 'Faculty of Arts',
        ]);

        $majors = $faculty->majors;

        $this->assertCount(0, $majors);
        $this->assertIsIterable($majors);
    }

    public function test_faculty_only_returns_its_own_majors()
    {
        $faculty1 = Faculty::create([
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $faculty2 = Faculty::create([
            'name' => 'Science',
            'code' => 'SCI',
        ]);

        $major1 = Major::create([
            'faculty_id' => $faculty1->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $major2 = Major::create([
            'faculty_id' => $faculty2->id,
            'name' => 'Physics',
            'code' => 'PHY',
        ]);

        $faculty1Majors = $faculty1->majors;
        $faculty2Majors = $faculty2->majors;

        $this->assertCount(1, $faculty1Majors);
        $this->assertCount(1, $faculty2Majors);
        $this->assertEquals($major1->id, $faculty1Majors->first()->id);
        $this->assertEquals($major2->id, $faculty2Majors->first()->id);
        $this->assertNotEquals($major1->id, $faculty2Majors->first()->id);
    }
}