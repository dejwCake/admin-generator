<?php

declare(strict_types=1);

namespace Database\Factories\Feed;

use App\Models\Feed\Article;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Article::class)]
final class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
        ];
    }
}
