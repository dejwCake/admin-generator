@php
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Collection;
    assert($relations instanceof RelationCollection);
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $modelNameSpace }};
@php
    $hasRoles = false;
    if($relations->hasBelongsToMany()) {
        $hasRoles = $relations->hasRelatedTableInBelongsToMany('roles');
    }
    $uses = new Collection([
        'Illuminate\Database\Eloquent\Factories\HasFactory',
        'Illuminate\Database\Eloquent\Model',
    ]);
    if ($hasSoftDelete) {
        $uses->push('Illuminate\Database\Eloquent\SoftDeletes');
    }
    if ($hasRoles) {
        $uses[] = 'Spatie\Permission\Traits\HasRoles';
    }
    if ($translatable->count() > 0) {
        $uses->push('Brackets\Translatable\Traits\HasTranslations');
    }
    if ($hasPublishedAt) {
        $uses->push('Brackets\Craftable\Traits\PublishableTrait');
    }
    if ($fillable) {
        foreach ($fillable as $fillableColumn) {
            if ($fillableColumn === "created_by_admin_user_id") {
                $uses->push('Brackets\Craftable\Traits\CreatedByAdminUserTrait');
            } elseif ($fillableColumn === "updated_by_admin_user_id") {
                $uses->push('Brackets\Craftable\Traits\UpdatedByAdminUserTrait');
            }
        }
    }
    if ($mediaCollections->isNotEmpty()) {
        $uses->push('Brackets\Media\HasMedia\AutoProcessMediaTrait');
        $uses->push('Brackets\Media\HasMedia\HasMediaCollectionsTrait');
        $uses->push('Brackets\Media\HasMedia\HasMediaThumbsTrait');
        $uses->push('Brackets\Media\HasMedia\ProcessMediaTrait');
        $uses->push('Spatie\MediaLibrary\HasMedia');
        if ($mediaCollections->contains(fn ($collection) => $collection->isImage())) {
            $uses->push('Spatie\MediaLibrary\MediaCollections\Models\Media');
        }
    }
    if (count($dates) > 0 || $hasCarbonProperty) {
        $uses->push('Carbon\CarbonInterface');
    }
    if ($relations->hasBelongsToManyWithoutRelatedTable('roles')) {
        $uses[] = 'Illuminate\Database\Eloquent\Relations\BelongsToMany';
        foreach ($relations->getBelongsToManyWithoutRelatedTable('roles') as $belongsToMany) {
            $relatedNamespace = implode('\\', array_slice(explode('\\', $belongsToMany->relatedModel), 0, -1));
            if ($relatedNamespace !== $modelNameSpace) {
                $uses->push($belongsToMany->relatedModel);
            }
        }
    }
    if ($relations->hasBelongsTo()) {
        $uses->push('Illuminate\Database\Eloquent\Relations\BelongsTo');
        foreach ($relations->getBelongsTo() as $belongsTo) {
            $relatedNamespace = implode('\\', array_slice(explode('\\', $belongsTo->relatedModel), 0, -1));
            if ($relatedNamespace !== $modelNameSpace) {
                $uses->push($belongsTo->relatedModel);
            }
        }
    }
    $uses = $uses->unique()->sort();
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

/**
@foreach($allColumns as $column)
 * @property {{ !$column['required'] ? $column['phpType'] . '|null' : $column['phpType'] }} ${{ $column['name'] }}
@endforeach
 */
final class {{ $modelBaseName }} extends Model{{ $mediaCollections->isNotEmpty() ? ' implements HasMedia' : '' }}
{
@php
    $traitUses = new Collection([
        'HasFactory'
    ]);
    if($hasSoftDelete) {
        $traitUses->push('SoftDeletes');
    }
    if($hasPublishedAt) {
        $traitUses->push('PublishableTrait');
    }
    if($hasRoles) {
        $traitUses->push('HasRoles');
    }
    if($translatable->count() > 0) {
        $traitUses->push('HasTranslations');
    }
    if ($fillable) {
        foreach ($fillable as $fillableColumn) {
            if ($fillableColumn === "created_by_admin_user_id") {
                $traitUses->push('CreatedByAdminUserTrait');
            } elseif ($fillableColumn === "updated_by_admin_user_id") {
                $traitUses->push('UpdatedByAdminUserTrait');
            }
        }
    }
    if ($mediaCollections->isNotEmpty()) {
        $traitUses->push('AutoProcessMediaTrait');
        $traitUses->push('HasMediaCollectionsTrait');
        $traitUses->push('HasMediaThumbsTrait');
        $traitUses->push('ProcessMediaTrait');
    }
    $traitUses = $traitUses->unique()->sort();
@endphp
@if(count($traitUses) > 0)
@foreach($traitUses as $traitUse)
    use {{ $traitUse }};
@endforeach
@endif
@if ($tableName !== null)

    protected $table = '{{ $tableName }}';
@endif
@if (count($fillable) > 0)

    /**
     * @var array<int, string>
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = [
@foreach($fillable as $fillableField)
        '{{ $fillableField }}',
@endforeach
    ];
@endif
@if (count($hidden) > 0)

    /**
     * @var array<int, string>
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $hidden = [
@foreach($hidden as $hiddenField)
        '{{ $hiddenField }}',
@endforeach
    ];
@endif
@if ($translatable->count() > 0)

    /**
     * These attributes are translatable
     *
     * @var array<int, string>
     */
    protected array $translatable = [
@foreach($translatable as $translatableField)
        '{{ $translatableField }}',
@endforeach
    ];
@endif
@if (!$hasTimestamps)

    public $timestamps = false;
@endif
@if ($relations->hasBelongsToManyWithoutRelatedTable('roles'))

@foreach($relations->getBelongsToManyWithoutRelatedTable('roles') as $belongsToMany)
    public function {{ $belongsToMany->relationMethodName }}(): BelongsToMany
    {
        return $this->belongsToMany({{ $belongsToMany->relatedModelName }}::class, '{{ $belongsToMany->relationTable }}', '{{ $belongsToMany->foreignKey }}', '{{ $belongsToMany->relatedKey }}');
    }
@if(!$loop->last)

@endif
@endforeach
@endif
@if($relations->hasBelongsTo())

@foreach($relations->getBelongsTo() as $belongsTo)
    public function {{ $belongsTo->relationMethodName }}(): BelongsTo
    {
        return $this->belongsTo({{ $belongsTo->relatedModelName }}::class);
    }
@if(!$loop->last)

@endif
@endforeach
@endif
@if($mediaCollections->isNotEmpty())

    public function registerMediaCollections(): void
    {
@foreach($mediaCollections as $collection)
        $this->addMediaCollection('{{ $collection->collectionName }}')
@if($collection->isPrivate())
            ->private()
@endif
            ->maxFilesize(10 * 1024 * 1024)
            ->maxNumberOfFiles({{ $collection->maxFiles }})
@if($collection->isImage())
            ->accepts('image/*');
@else
            ->accepts('application/pdf', 'application/zip', 'application/x-zip');
@endif
@if(!$loop->last)

@endif
@endforeach
    }
@endif
@if($mediaCollections->contains(fn ($collection) => $collection->isImage()))

    /**
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->autoRegisterThumb200();
@foreach($mediaCollections as $collection)
@if($collection->isImage())

        $converted = [
            'name' => 'converted',
            'collection' => '{{ $collection->collectionName }}',
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
            'collection' => '{{ $collection->collectionName }}',
        ];

        $this->addMediaConversion($original['name'])
            ->performOnCollections($original['collection'])
            ->keepOriginalImageFormat()
            ->nonQueued();

        $this->addMediaConversion($original['name'] . 'Retina')
            ->performOnCollections($original['collection'])
            ->keepOriginalImageFormat()
            ->nonQueued();
@endif
@endforeach
    }
@endif
@if (count($dates) > 0 || count($booleans) > 0)

    /**
     * @return array<string>
     */
    protected function casts(): array
    {
        return [
@foreach($booleans as $boolean)
            '{{ $boolean }}' => 'boolean',
@endforeach
@foreach($dates as $date)
            '{{ $date }}' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
@endforeach
        ];
    }
@endif
}
