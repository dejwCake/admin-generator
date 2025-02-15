<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Billing\MyCat;
use Illuminate\Database\Eloquent\Factories\Factory;

class MyCatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $model = MyCat::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
        ];
    }
}
