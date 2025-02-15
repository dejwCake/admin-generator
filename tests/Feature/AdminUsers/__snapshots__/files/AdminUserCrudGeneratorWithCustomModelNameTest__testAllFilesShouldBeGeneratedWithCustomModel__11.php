<?php

declare(strict_types=1);

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => bcrypt($this->faker->password),
            'remember_token' => null,
            'activated' => true,
            'forbidden' => $this->faker->boolean(),
            'language' => 'en',
            'deleted_at' => null,
            'created_at' => $this->faker->sentence,
            'updated_at' => $this->faker->sentence,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function notActivated(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => [
            'activated' => false,
        ]);
    }
}
