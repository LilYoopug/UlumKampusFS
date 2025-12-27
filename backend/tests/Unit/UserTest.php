<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Faculty;
use App\Models\Major;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_faculty_relationship()
    {
        $faculty = Faculty::create([
            'name' => 'Science',
            'code' => 'SCI',
            'description' => 'Faculty of Science',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'faculty_id' => $faculty->id,
        ]);

        $this->assertInstanceOf(Faculty::class, $user->faculty);
        $this->assertEquals($faculty->id, $user->faculty->id);
    }

    public function test_user_has_major_relationship()
    {
        $faculty = Faculty::create([
            'name' => 'Science',
            'code' => 'SCI',
        ]);

        $major = Major::create([
            'faculty_id' => $faculty->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'major_id' => $major->id,
        ]);

        $this->assertInstanceOf(Major::class, $user->major);
        $this->assertEquals($major->id, $user->major->id);
    }

    public function test_user_has_gpa_field()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'gpa' => 3.85,
        ]);

        $this->assertEquals(3.85, $user->gpa);
    }

    public function test_user_has_student_id_field()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'student_id' => '202500001',
        ]);

        $this->assertEquals('202500001', $user->student_id);
    }

    public function test_user_has_enrollment_year_field()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'enrollment_year' => 2023,
        ]);

        $this->assertEquals(2023, $user->enrollment_year);
    }

    public function test_user_has_graduation_year_field()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'graduation_year' => 2027,
        ]);

        $this->assertEquals(2027, $user->graduation_year);
    }

    public function test_user_has_phone_field()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'phone' => '+1234567890',
        ]);

        $this->assertEquals('+1234567890', $user->phone);
    }

    public function test_user_has_address_field()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'address' => '123 Main St, City',
        ]);

        $this->assertEquals('123 Main St, City', $user->address);
    }

    public function test_user_gpa_defaults_to_null()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $this->assertNull($user->gpa);
    }

    public function test_user_student_id_is_unique()
    {
        User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'student_id' => '202500001',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'student_id' => '202500001',
        ]);
    }
}