<?php

declare(strict_types=1);

namespace App\Models;

use Brackets\Craftable\Traits\CreatedByAdminUserTrait;
use Brackets\Craftable\Traits\PublishableTrait;
use Brackets\Craftable\Traits\UpdatedByAdminUserTrait;
use Brackets\Translatable\Traits\HasTranslations;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $perex
 * @property CarbonInterface|null $published_at
 * @property CarbonInterface|null $date_start
 * @property string|null $time_start
 * @property CarbonInterface|null $date_time_end
 * @property array $text
 * @property array $description
 * @property bool $enabled
 * @property bool $send
 * @property float|null $price
 * @property int $views
 * @property int|null $created_by_admin_user_id
 * @property int|null $updated_by_admin_user_id
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $deleted_at
 */
final class Category extends Model
{
    use CreatedByAdminUserTrait;
    use HasFactory;
    use HasTranslations;
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
        'slug',
        'perex',
        'published_at',
        'date_start',
        'time_start',
        'date_time_end',
        'text',
        'description',
        'enabled',
        'send',
        'price',
        'views',
        'created_by_admin_user_id',
        'updated_by_admin_user_id',
    ];

    /**
     * These attributes are translatable
     *
     * @var array<int, string>
     */
    protected array $translatable = [
        'text',
        'description',
    ];

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
            'created_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'updated_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'deleted_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
        ];
    }
}
