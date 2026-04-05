@php
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Arr;
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
    $uses = [
        'Brackets\AdminAuth\Activation\Traits\CanActivate',
        'Brackets\AdminAuth\Activation\Contracts\CanActivate as CanActivateContract',
        'Brackets\AdminAuth\Notifications\ResetPassword',
        'Illuminate\Database\Eloquent\Factories\HasFactory',
        'Illuminate\Foundation\Auth\User as Authenticatable',
        'Illuminate\Notifications\Notifiable',
    ];
    if ($hasSoftDelete) {
        $uses[] = 'Illuminate\Database\Eloquent\SoftDeletes';
    }
    if ($hasRoles) {
        $uses[] = 'Spatie\Permission\Traits\HasRoles';
    }
    if ($translatable->count() > 0) {
        $uses[] = 'Brackets\Translatable\Traits\HasTranslations';
    }
    if (count($dates) > 0 || $hasCarbonProperty) {
        $uses[] = 'Carbon\CarbonInterface';
    }
    if ($relations->hasBelongsToManyWithoutRelatedTable('roles')) {
        $uses[] = 'Illuminate\Database\Eloquent\Relations\BelongsToMany';
        foreach ($relations->getBelongsToManyWithoutRelatedTable('roles') as $belongsToMany) {
            $relatedNamespace = implode('\\', array_slice(explode('\\', $belongsToMany->relatedModel), 0, -1));
            if ($relatedNamespace !== $modelNameSpace) {
                $uses[] = $belongsToMany->relatedModel;
            }
        }
    }
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

/**
@foreach($allColumns as $column)
 * @property {{ !$column['required'] ? $column['phpType'] . '|null' : $column['phpType'] }} ${{ $column['name'] }}
@endforeach
 */
final class {{ $modelBaseName }} extends Authenticatable implements CanActivateContract
{
@php
    $traitUses = [
        'CanActivate',
        'HasFactory',
        'Notifiable',
    ];
    if($hasSoftDelete) {
        $traitUses[] = 'SoftDeletes';
    }
    if($hasRoles) {
        $traitUses[] = 'HasRoles';
    }
    if($translatable->count() > 0) {
        $traitUses[] = 'HasTranslations';
    }
    $traitUses = Arr::sort($traitUses);
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
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

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $appends = [
        'full_name',
    ];
@if (!$hasTimestamps)

    public $timestamps = false;
@endif

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(app(ResetPassword::class, ['token' => $token]));
    }
@if ($relations->hasBelongsToManyWithoutRelatedTable('roles'))

@foreach($relations->getBelongsToManyWithoutRelatedTable('roles') as $belongsToMany)
    public function {{ $belongsToMany->relatedTable }}(): BelongsToMany
    {
        return $this->belongsToMany({{ $belongsToMany->relatedModelName }}::class, '{{ $belongsToMany->relationTable }}', '{{ $belongsToMany->foreignKey }}', '{{ $belongsToMany->relatedKey }}');
    }
@endforeach
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
