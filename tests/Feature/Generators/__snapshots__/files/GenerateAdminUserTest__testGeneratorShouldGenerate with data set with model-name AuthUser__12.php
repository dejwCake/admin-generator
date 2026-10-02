<?php

declare(strict_types=1);

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use Illuminate\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
#[UseModel(User::class)]
final class UserFactory extends Factory
{
    public function definition(): array
    {
        $hasher = Container::getInstance()->make(Hasher::class);

        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->email,
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
        return $this->state(static fn (): array => ['activated' => true]);
    }

    public function notActivated(): self
    {
        return $this->state(static fn (): array => ['activated' => false]);
    }

    public function forbidden(): self
    {
        return $this->state(static fn (): array => ['forbidden' => true]);
    }

    public function notForbidden(): self
    {
        return $this->state(static fn (): array => ['forbidden' => false]);
    }
}
