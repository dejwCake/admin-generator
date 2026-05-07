<?php

declare(strict_types=1);

namespace Database\Factories\Feed;

use App\Feed\Post;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Post::class)]
final class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
        ];
    }
}
