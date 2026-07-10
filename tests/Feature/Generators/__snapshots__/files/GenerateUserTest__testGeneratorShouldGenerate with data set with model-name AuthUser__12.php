<?php

declare(strict_types=1);

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use Illuminate\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(User::class)]
final class UserFactory extends Factory
{
    public function definition(): array
    {
        $hasher = Container::getInstance()->make(Hasher::class);

        return [
            'name' => $this->faker->firstName,
            'email' => $this->faker->email,
            'email_verified_at' => $this->faker->dateTime,
            'password' => $hasher->make($this->faker->password),
            'remember_token' => null,
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,
        ];
    }

    public function withPassword(string $password): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic, SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes): array => [
            'password' => Container::getInstance()->make(Hasher::class)->make($password),
        ]);
    }

    public function unverified(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic, SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }

    public function withTwoFactor(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => $this->faker->text(),
            'two_factor_recovery_codes' => $this->faker->text(),
            'two_factor_confirmed_at' => $this->faker->dateTime,
        ]);
    }
}
