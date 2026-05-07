<?php

declare(strict_types=1);

namespace Database\Factories\Brackets\AdminAuth\Models;

use Brackets\AdminAuth\Models\AdminUser;
use Illuminate\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(AdminUser::class)]
final class AdminUserFactory extends Factory
{
    public function definition(): array
    {
        $hasher = Container::getInstance()->make(Hasher::class);

        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => $hasher->make($this->faker->password),
            'remember_token' => null,
            'activated' => $this->faker->boolean(),
            'forbidden' => $this->faker->boolean(),
            'language' => 'en',
            'deleted_at' => null,
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,
        ];
    }

    public function activated(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes) => ['activated' => true]);
    }

    public function notActivated(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes) => ['activated' => false]);
    }

    public function forbidden(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes) => ['forbidden' => true]);
    }

    public function notForbidden(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes) => ['forbidden' => false]);
    }
}
