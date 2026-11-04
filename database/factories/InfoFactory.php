<?php

use App\User;
use App\Http\Models\Info;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Info::class, function (Faker $faker) {
    return [
        'title' => $faker->unique()->sentence(4),
        'content' => $faker->paragraph,
        'author' => function () {
            return factory(User::class)->states('editor')->create()->id;
        },
        'status' => 'public',
    ];
});

$factory->state(Info::class, 'private', ['status' => 'private']);
