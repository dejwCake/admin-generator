<?php

declare(strict_types=1);

namespace App\Models;

use Brackets\Craftable\Traits\CreatedByAdminUserTrait;
use Brackets\Craftable\Traits\PublishableTrait;
use Brackets\Craftable\Traits\UpdatedByAdminUserTrait;
use Brackets\Media\HasMedia\AutoProcessMediaTrait;
use Brackets\Media\HasMedia\HasMediaCollectionsTrait;
use Brackets\Media\HasMedia\HasMediaThumbsTrait;
use Brackets\Media\HasMedia\ProcessMediaTrait;
use Brackets\Translatable\Traits\HasTranslations;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
 * @property array|null $long_text
 * @property CarbonInterface|null $published_at
 * @property CarbonInterface|null $date_start
 * @property string|null $time_start
 * @property CarbonInterface|null $date_time_end
 * @property CarbonInterface $released_at
 * @property array $text
 * @property array $description
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
 */
final class Category extends Model implements HasMedia
{
    use AutoProcessMediaTrait;
    use CreatedByAdminUserTrait;
    use HasFactory;
    use HasMediaCollectionsTrait;
    use HasMediaThumbsTrait;
    use HasTranslations;
    use ProcessMediaTrait;
    use PublishableTrait;
    use SoftDeletes;
    use UpdatedByAdminUserTrait;

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = [
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
    ];

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * These attributes are translatable
     *
     * @var array<int, string>
     */
    protected array $translatable = [
        'long_text',
        'text',
        'description',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'category_post', 'category_id', 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->maxFilesize(10 * 1024 * 1024)
            ->maxNumberOfFiles(5000)
            ->accepts('image/*');
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->autoRegisterThumb200();

        $converted = [
            'name' => 'converted',
            'collection' => 'gallery',
            'width' => 960,
            'height' => 360,
        ];

        $this->addMediaConversion($converted['name'])
            ->width($converted['width'])
            ->height($converted['height'])
            ->crop($converted['width'], $converted['height'])
            ->performOnCollections($converted['collection'])
            ->keepOriginalImageFormat()
            ->nonQueued();

        $this->addMediaConversion($converted['name'] . 'Retina')
            ->width(2 * $converted['width'])
            ->height(2 * $converted['height'])
            ->crop(2 * $converted['width'], 2 * $converted['height'])
            ->performOnCollections($converted['collection'])
            ->keepOriginalImageFormat()
            ->nonQueued();

        $original = [
            'name' => 'original',
            'collection' => 'gallery',
        ];

        $this->addMediaConversion($original['name'])
            ->performOnCollections($original['collection'])
            ->keepOriginalImageFormat()
            ->nonQueued();

        $this->addMediaConversion($original['name'] . 'Retina')
            ->performOnCollections($original['collection'])
            ->keepOriginalImageFormat()
            ->nonQueued();
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
            'date_start' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'date_time_end' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'released_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'created_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'updated_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'deleted_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
        ];
    }
}
