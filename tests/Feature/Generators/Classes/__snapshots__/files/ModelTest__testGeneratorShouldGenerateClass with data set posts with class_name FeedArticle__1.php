<?php

declare(strict_types=1);

namespace App\Models\Feed;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $title
 */
final class Article extends Model
{
    use HasFactory;

    protected $table = 'posts';

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = [
        'title',
    ];

    public $timestamps = false;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_post', 'post_id', 'category_id');
    }
}
