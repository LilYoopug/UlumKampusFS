<?php

namespace Tests\Unit\Models;

use App\Models\Faculty;
use App\Models\Major;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MajorTest extends TestCase
{
    use RefreshDatabase;

    public function test_major_has_faculty_relationship()
    {
        $faculty = Faculty::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Faculty of Engineering',
        ]);

        $major = Major::create([
            'faculty_id' => $faculty->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $this->assertInstanceOf(Faculty::class, $major->faculty);
        $this->assertEquals($faculty->id, $major->faculty->id);
        $this->assertEquals('Engineering', $major->faculty->name);
    }

    public function test_major_returns_null_faculty_when_faculty_deleted()
    {
        $faculty = Faculty::create([
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $major = Major::create([
            'faculty_id' => $faculty->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $facultyId = $faculty->id;
        $faculty->delete();

        $major->refresh();
        $this->assertNull($major->faculty);
    }

    public function test_major_faculty_relationship_returns_correct_faculty()
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

        $this->assertEquals($faculty1->id, $major1->faculty->id);
        $this->assertEquals('Engineering', $major1->faculty->name);
        $this->assertEquals($faculty2->id, $major2->faculty->id);
        $this->assertEquals('Science', $major2->faculty->name);
    }

    public function test_major_faculty_attributes_accessible()
    {
        $faculty = Faculty::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Faculty of Engineering',
            'dean_name' => 'Dr. Smith',
            'email' => 'eng@university.edu',
            'phone' => '555-1234',
        ]);

        $major = Major::create([
            'faculty_id' => $faculty->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $this->assertEquals('Engineering', $major->faculty->name);
        $this->assertEquals('ENG', $major->faculty->code);
        $this->assertEquals('Faculty of Engineering', $major->faculty->description);
        $this->assertEquals('Dr. Smith', $major->faculty->dean_name);
        $this->assertEquals('eng@university.edu', $major->faculty->email);
        $this->assertEquals('555-1234', $major->faculty->phone);
    }
}