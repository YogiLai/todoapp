<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'text' => $faker->text(),
        'is_completed' => Task::NOT_COMPLETED
    ];
});
