<?php

declare(strict_types=1);

namespace App\Models;

use Brackets\Craftable\Traits\CreatedByAdminUserTrait;
use Brackets\Craftable\Traits\PublishableTrait;
use Brackets\Craftable\Traits\UpdatedByAdminUserTrait;
use Brackets\Translatable\Traits\HasTranslations;
use Carbon\CarbonInterface;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 * @property string|null $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $subject
 * @property string|null $email
 * @property string|null $password
 * @property string|null $remember_token
 * @property string $language
 * @property string $slug
 * @property string|null $perex
 * @property string|null $long_text
 * @property CarbonInterface|null $published_at
 * @property CarbonInterface|null $published_to
 * @property CarbonInterface|null $date_start
 * @property string|null $time_start
 * @property CarbonInterface|null $date_time_end
 * @property CarbonInterface $released_at
 * @property string $text
 * @property array<int, string> $description
 * @property bool $enabled
 * @property bool $send
 * @property float|null $price
 * @property float|null $rating
 * @property int $views
 * @property int|null $created_by_admin_user_id
 * @property int|null $updated_by_admin_user_id
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $deleted_at
 * @property-read Collection<int, Post> $posts
 * @property-read User|null $user
 */
#[Fillable([
    'user_id',
    'title',
    'name',
    'first_name',
    'last_name',
    'subject',
    'email',
    'password',
    'language',
    'slug',
    'perex',
    'long_text',
    'published_at',
    'published_to',
    'date_start',
    'time_start',
    'date_time_end',
    'released_at',
    'text',
    'description',
    'enabled',
    'send',
    'price',
    'rating',
    'views',
    'created_by_admin_user_id',
    'updated_by_admin_user_id',
])]
#[Hidden([
    'password',
    'remember_token',
])]
#[UseFactory(CategoryFactory::class)]
final class Category extends Model
{
    use CreatedByAdminUserTrait;
    use HasFactory;
    use HasTranslations;
    use PublishableTrait;
    use SoftDeletes;
    use UpdatedByAdminUserTrait;

    /**
     * These attributes are translatable
     *
     * @var array<int, string>
     */
    protected array $translatable = [
        'text',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'category_post', 'category_id', 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'send' => 'boolean',
            'published_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'published_to' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'date_start' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'date_time_end' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'released_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'created_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'updated_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'deleted_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'description' => 'array',
        ];
    }
}
