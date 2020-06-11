<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\User;
use App\Task;

class TasksTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user = null;

    protected function setUp():void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTask()
    {
        $this->browse(function(Browser $browser){
            $browser->loginAs($this->user)
                ->visit('/home')
                ->assertSee('Tasks');

            $browser->waitForText('Tasks')
                ->type('@task-input', 'First Task')
                ->click('@task-submit')
                ->waitForText('Tasks')
                ->pause(1000)
                ->assertSee('First Task');

            $browser->type('@task-input', 'Second Task')
                ->press('@task-submit')
                ->waitForText('Second Task')
                ->assertSee('Second Task');

            $this->assertDatabaseHas('tasks', ['text' => 'First Task']);
            $this->assertDatabaseHas('tasks', ['text' => 'Second Task']);

        });
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteTask()
    {
        $task = factory(Task::class)->create([
            'text' => 'Test Task',
            'user_id' => $this->user->id
        ]);
        $this->withExceptionHandling();
        $this->browse(function(Browser $browser){
            $browser->loginAs($this->user)
                ->visit('/home')
                ->waitForText('Tasks');
            $browser->click('@remove-task1')
                ->pause(1000)
                ->assertDontSee('Test Task');
        });

        $this->assertDatabaseMissing('tasks', $task->only(['id', 'text']));
    }

    /**
     * @throws \Throwable
     */
    public function testCompleteTask()
    {
        $task = factory(Task::class)
            ->create(['user_id'=>$this->user->id]);

        $this->browse(function(Browser $browser) use($task){
            $browser->loginAs($this->user)
                ->visit('/home')
                ->waitForText('Tasks')
                ->click("@check-task{$task->first()->id}")
                ->waitFor('.line-through');
        });
        $this->assertNotEmpty($task->fresh()->is_completed);
    }

}
