<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = [
        'title',
    ];

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $appends = ['resource_url'];

    public $timestamps = false;

    public function getResourceUrlAttribute(): string
    {
        return url('/admin/categories/' . $this->getKey());
    }

    public function posts(): BelongsToMany {
        return $this->belongsToMany(App\Models\Post::class, 'category_post', 'category_id', 'post_id');
    }
}
