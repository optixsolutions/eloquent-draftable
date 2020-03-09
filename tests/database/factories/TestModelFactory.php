<?php

use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Optix\Draftable\Tests\TestModel;

/** @var Factory $factory */
$factory->define(TestModel::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->state(TestModel::class, 'published', function () {
    return [
        'published_at' => Carbon::now(),
    ];
});

$factory->state(TestModel::class, 'scheduled', function () {
    return [
        'published_at' => Carbon::now()->addDay(),
    ];
});
