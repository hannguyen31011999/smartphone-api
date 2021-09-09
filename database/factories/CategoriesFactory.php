<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(App\Models\Categories::class, function (Faker $faker) {
    return [
        'categories_name' => $faker->username,     
        'categories_desc' => $faker->address,
    ];
});
