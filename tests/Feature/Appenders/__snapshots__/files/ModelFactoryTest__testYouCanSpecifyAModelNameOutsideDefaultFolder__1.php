<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Billing\MyCat::class, static function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        
        
    ];
});
