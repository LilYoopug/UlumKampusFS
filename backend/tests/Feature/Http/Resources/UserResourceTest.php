<?php

namespace Tests\Feature\Http\Resources;

use App\Http\Resources\UserResource;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_transforms_user_model_to_resource(): void
    {
        $faculty = Faculty::factory()->create();
        $major = Major::factory()->create(['faculty_id' => $faculty->id]);
        $user = User::factory()->create([
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'role' => 'student',
        ]);

        $resource = new UserResource($user);
        $array = $resource->toArray(request());

        $this->assertEquals($user->id, $array['id']);
        $this->assertEquals($user->name, $array['name']);
        $this->assertEquals($user->email, $array['email']);
        $this->assertEquals($user->role, $array['role']);
        $this->assertEquals($user->faculty_id, $array['faculty_id']);
        $this->assertEquals($user->major_id, $array['major_id']);
        $this->assertEquals($user->student_id, $array['student_id']);
        $this->assertEquals($user->gpa, $array['gpa']);
        $this->assertEquals($user->enrollment_year, $array['enrollment_year']);
        $this->assertEquals($user->graduation_year, $array['graduation_year']);
        $this->assertEquals($user->phone, $array['phone']);
        $this->assertEquals($user->address, $array['address']);
    }

    public function test_excludes_sensitive_fields(): void
    {
        $user = User::factory()->create();

        $resource = new UserResource($user);
        $array = $resource->toArray(request());

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_includes_nested_resources(): void
    {
        $faculty = Faculty::factory()->create();
        $major = Major::factory()->create(['faculty_id' => $faculty->id]);
        $user = User::factory()->create([
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
        ]);

        $resource = new UserResource($user);
        $array = $resource->toArray(request());

        $this->assertArrayHasKey('faculty', $array);
        $this->assertArrayHasKey('major', $array);
        $this->assertEquals($faculty->id, $array['faculty']['id']);
        $this->assertEquals($major->id, $array['major']['id']);
    }

    public function test_collection_transforms_multiple_users(): void
    {
        $users = User::factory()->count(3)->create();

        $collection = UserResource::collection($users);
        $array = $collection->toArray(request());

        $this->assertCount(3, $array);
        $this->assertEquals($users->first()->id, $array[0]['id']);
        $this->assertEquals($users->last()->id, $array[2]['id']);
    }
}