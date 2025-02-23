<?php

declare(strict_types=1);

namespace App\Models;

use Brackets\Craftable\Traits\CreatedByAdminUserTrait;
use Brackets\Craftable\Traits\UpdatedByAdminUserTrait;
use Brackets\Translatable\Traits\HasTranslations;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use CreatedByAdminUserTrait;
    use HasFactory;
    use HasTranslations;
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translatable = [
        'text',
        'description',
    ];

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $appends = ['resource_url'];

    public function getResourceUrlAttribute(): string
    {
        return url('/admin/categories/' . $this->getKey());
    }

    /**
     * @return array<string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'date_start' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'date_time_end' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'created_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'updated_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'deleted_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
        ];
    }
}
