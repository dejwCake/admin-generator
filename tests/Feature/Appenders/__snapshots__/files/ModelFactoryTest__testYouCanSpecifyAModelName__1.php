<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Billing\Cat::class, static function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        
        
    ];
});
