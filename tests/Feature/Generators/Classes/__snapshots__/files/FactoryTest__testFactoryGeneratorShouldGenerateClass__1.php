<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Category::class)]
final class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->randomNumber(5),
            'title' => $this->faker->sentence,
            'slug' => $this->faker->unique()->slug,
            'perex' => $this->faker->text(),
            'published_at' => $this->faker->date(),
            'date_start' => $this->faker->date(),
            'time_start' => $this->faker->time(),
            'date_time_end' => $this->faker->dateTime,
            'enabled' => $this->faker->boolean(),
            'send' => $this->faker->boolean(),
            'price' => $this->faker->randomFloat(2, max: 10000),
            'views' => $this->faker->randomNumber(5),
            'created_by_admin_user_id' => $this->faker->randomNumber(5),
            'updated_by_admin_user_id' => $this->faker->randomNumber(5),
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,
            'deleted_at' => null,
            'text' => ['en' => $this->faker->sentence],
            'description' => ['en' => $this->faker->sentence],
        ];
    }

    public function enabled(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['enabled' => true]);
    }

    public function notEnabled(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['enabled' => false]);
    }

    public function send(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['send' => true]);
    }

    public function notSend(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['send' => false]);
    }

    public function notPublished(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['published_at' => null]);
    }
}
