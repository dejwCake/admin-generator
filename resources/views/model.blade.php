@php echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $modelNameSpace }};
@php
    $hasRoles = false;
    if(count($relations) && count($relations['belongsToMany'])) {
        $hasRoles = $relations['belongsToMany']->filter(function($belongsToMany) {
            return $belongsToMany['related_table'] == 'roles';
        })->count() > 0;
        $relations['belongsToMany'] = $relations['belongsToMany']->reject(function($belongsToMany) {
            return $belongsToMany['related_table'] == 'roles';
        });
    }
@endphp

@if($fillable)@foreach($fillable as $fillableColumn)
@if($fillableColumn === "created_by_admin_user_id")use Brackets\Craftable\Traits\CreatedByAdminUserTrait;
@elseif($fillableColumn === "updated_by_admin_user_id")use Brackets\Craftable\Traits\UpdatedByAdminUserTrait;
@endif
@endforeach
@endif
@if($translatable->count() > 0)use Brackets\Translatable\Traits\HasTranslations;
@endif
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
@if($hasSoftDelete)use Illuminate\Database\Eloquent\SoftDeletes;
@endif
@if (isset($relations['belongsToMany']) && count($relations['belongsToMany']))
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
@endif
@if($hasRoles)use Spatie\Permission\Traits\HasRoles;
@endif

/**
@foreach($fillable as $f)
 * @property ${{ $f }}
@endforeach
@if ($dates)
@foreach($dates as $date)
 * @property Carbon ${{ $date }}
@endforeach
@endif
 * @property string $admin_edit_url
 * @property string $admin_update_url
 * @property string $admin_delete_url
 */

class {{ $modelBaseName }} extends Model
{
    use HasFactory;
@if($hasSoftDelete)
    use SoftDeletes;
@endif
@if($hasRoles)use HasRoles;
@endif
@if($translatable->count() > 0)use HasTranslations;
@endif
@if($fillable)@foreach($fillable as $fillableColumn)
@if($fillableColumn === "created_by_admin_user_id")use CreatedByAdminUserTrait;
@elseif($fillableColumn === "updated_by_admin_user_id")    use UpdatedByAdminUserTrait;
@endif
@endforeach
@endif
    @if (!is_null($tableName))protected $table = '{{ $tableName }}';

    @endif
@if ($fillable)/**
     * {{'@'}}var array{{'<'}}string>
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = [
    @foreach($fillable as $f)
        '{{ $f }}',
    @endforeach
    ];
    @endif

    @if ($hidden && count($hidden) > 0)/**
     * {{'@'}}var array{{'<'}}string>
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $hidden = [
    @foreach($hidden as $h)
    '{{ $h }}',
    @endforeach
    ];
    @endif

    @if ($dates)/**
    * {{'@'}}var array{{'<'}}string, string>
    * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
    */
    protected $casts = [
    @foreach($dates as $date)
        '{{ $date }}' => 'datetime',
    @endforeach
    ];
    @endif

@if ($translatable->count() > 0)// these attributes are translatable
    /**
     * {{'@'}}var array{{'<'}}string>
     */
    public array $translatable = [
    @foreach($translatable as $translatableField)
    '{{ $translatableField }}',
    @endforeach
    ];
    @endif
@if (!$timestamps)public $timestamps = false;
    @endif

    /**
     * {{'@'}}var array{{'<'}}string, string>
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $appends = ['admin_edit_url', 'admin_update_url', 'admin_delete_url'];


    /* ************************ ACCESSOR ************************* */

    public function getAdminEditUrlAttribute(): string
    {

        return route('admin/{{ $resource }}/edit', ['{{ $variableName }}' => $this->getKey()]);
    }

    public function getAdminUpdateUrlAttribute(): string
    {
        return route('admin/{{ $resource }}/update', ['{{ $variableName }}' => $this->getKey()]);
    }

    public function getAdminDeleteUrlAttribute(): string
    {
        return route('admin/{{ $resource }}/destroy', ['{{ $variableName }}' => $this->getKey()]);
    }
@if (count($relations))

    /* ************************ RELATIONS ************************ */
@if (count($relations['belongsToMany']))
@foreach($relations['belongsToMany'] as $belongsToMany)/**
    * Relation to {{ $belongsToMany['related_model_name_plural'] }}
    *
    * {{'@'}}return BelongsToMany
    */
    public function {{ $belongsToMany['related_table'] }}() {
        return $this->belongsToMany({{ $belongsToMany['related_model_class'] }}, '{{ $belongsToMany['relation_table'] }}', '{{ $belongsToMany['foreign_key'] }}', '{{ $belongsToMany['related_key'] }}');
    }
@endforeach
@endif
@endif}
