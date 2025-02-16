@php use Illuminate\Support\Arr;echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $modelNameSpace }};
@php
    $hasRoles = false;
    if(count($relations) && count($relations['belongsToMany'])) {
        $hasRoles = $relations['belongsToMany']->filter(function($belongsToMany) {
            return $belongsToMany['related_table'] === 'roles';
        })->count() > 0;
        $relations['belongsToMany'] = $relations['belongsToMany']->reject(function($belongsToMany) {
            return $belongsToMany['related_table'] === 'roles';
        });
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
        $uses = array_merge($uses, [
            'Illuminate\Database\Eloquent\SoftDeletes',
        ]);
    }
    if ($hasRoles) {
        $uses = array_merge($uses, [
            'Spatie\Permission\Traits\HasRoles',
        ]);
    }
    if ($translatable->count() > 0) {
        $uses = array_merge($uses, [
            'Brackets\Translatable\Traits\HasTranslations',
        ]);
    }
    if (count($dates) > 0) {
        $uses = array_merge($uses, [
            'Carbon\CarbonInterface',
        ]);
    }
    if (isset($relations['belongsToMany']) && count($relations['belongsToMany'])) {
        $uses[] = 'Illuminate\Database\Eloquent\Relations\BelongsToMany';
    }
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

class {{ $modelBaseName }} extends Authenticatable implements CanActivateContract
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
     * these attributes are translatable
     *
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    public $translatable = [
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
        'resource_url',
    ];

@if (!$timestamps)
    public $timestamps = false;

@endif
    public function getResourceUrlAttribute(): string {
        return url('/admin/{{$resource}}/' . $this->getKey());
    }

    public function getFullNameAttribute(): string {
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

@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)
@foreach($relations['belongsToMany'] as $belongsToMany)
    public function {{ $belongsToMany['related_table'] }}(): BelongsTo {
        return $this->belongsToMany({{ $belongsToMany['related_model_class'] }}, '{{ $belongsToMany['relation_table'] }}', '{{ $belongsToMany['foreign_key'] }}', '{{ $belongsToMany['related_key'] }}');
    }

@endforeach
@endif
@if (count($dates) > 0)
    /**
     * @return array<string>
     */
    protected function casts(): array
    {
        return [
@foreach($dates as $date)
            '{{ $date }}' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
@endforeach
        ];
    }
@endif
}
