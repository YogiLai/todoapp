<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\Task;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TasksTest extends TestCase
{
    use DatabaseMigrations;

    public function testUserCanCreateTask()
    {
        $user = Factory(User::class)->create();
        $task = [
            'text' => 'new task text',
            'user_id' => $user->id
        ];
        $this->withoutExceptionHandling();
        \Laravel\Passport\Passport::actingAs($user, ['*']);
        $response = $this->json('post','api/task', $task);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', $task);
    }

    public function testGuestCantCreateTask()
    {
        $task = [
            'text' => 'new task',
            'user_id' => 1
        ];
        //$this->withoutExceptionHandling();
        $response = $this->json('post', 'api/task', $task);
        $response->assertStatus(401);
        $this->assertDatabaseMissing('tasks', $task);
    }

    public function testUserCanDeleteTask()
    {
        $user = factory(User::class)->create();
        $task = factory(Task::class)->create([
            'text' => 'task to delete',
            'user_id' => $user->id
        ]);

        \Laravel\Passport\Passport::actingAs($user, ['*']);
        $response = $this->json('DELETE', 'api/task/' . $task->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);

    }

    public function testUserCanCompleteTask()
    {
        $user = factory(User::class)->create();
        $task = factory(Task::class)->create([
            'text' => 'task to complete',
            'user_id' => $user->id
        ]);

        \Laravel\Passport\Passport::actingAs($user, ['*']);
        $response = $this->json('PUT', 'api/task/' . $task->id, [
            'is_completed' => Task::IS_COMPLETED
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($task->fresh()->is_completed);


    }

}
