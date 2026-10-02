<?php

declare(strict_types=1);

namespace App\Models\Feed;

use App\Models\Category;
use Database\Factories\Feed\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $title
 * @property-read Collection<int, Category> $categories
 */
#[Fillable([
    'title',
])]
#[Table(name: 'posts', timestamps: false)]
#[UseFactory(ArticleFactory::class)]
final class Article extends Model
{
    use HasFactory;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_post', 'post_id', 'category_id');
    }
}
