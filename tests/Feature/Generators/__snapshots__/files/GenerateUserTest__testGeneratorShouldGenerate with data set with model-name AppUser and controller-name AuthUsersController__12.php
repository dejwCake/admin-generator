<?php

declare(strict_types=1);

namespace Database\Factories;

use App\User;
use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
#[UseModel(User::class)]
final class UserFactory extends Factory
{
    /**
     * @return array<string, DateTime|string|null>
     */
    public function definition(): array
    {
        $hasher = Container::getInstance()->make(Hasher::class);

        return [
            'name' => $this->faker->firstName,
            'email' => $this->faker->email,
            'email_verified_at' => $this->faker->dateTime,
            'password' => $hasher->make($this->faker->password),
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter,SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        $encrypter = Container::getInstance()->make(Encrypter::class);

        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter,SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => $encrypter->encrypt('secret'),
            'two_factor_recovery_codes' => $encrypter->encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => CarbonImmutable::now(),
        ]);
    }

    /**
     * Indicate that the user has a known password (defaults to "password").
     */
    public function withPassword(string $password = 'password'): static
    {
        $hasher = Container::getInstance()->make(Hasher::class);

        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter,SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        return $this->state(fn (array $attributes) => [
            'password' => $hasher->make($password),
        ]);
    }
}
