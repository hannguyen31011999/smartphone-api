<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(App\Models\Visitor::class, function (Faker $faker) {
    return [
        'ip_guest' => '1.52.213.139',
    ];
});
