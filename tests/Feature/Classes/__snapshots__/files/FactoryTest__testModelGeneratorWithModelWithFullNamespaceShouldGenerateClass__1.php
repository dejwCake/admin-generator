<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Billing\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     */
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
            'price' => $this->faker->randomFloat(2),
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
}
