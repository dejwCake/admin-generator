@php echo "<?php"
@endphp


declare(strict_types=1);

namespace App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }};
@php
    if($translatable->count() > 0) {
        $translatableColumns = $columns->filter(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
        $standardColumn = $columns->reject(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
    }
@endphp

use ArondeParon\RequestSanitizer\Traits\SanitizesInputs;
use Brackets\AdminUI\Http\Requests\Sanitizers\StringToArray;
use Brackets\AdminUI\Http\Requests\Traits\Validated;
@if($translatable->count() > 0)use Brackets\Translatable\TranslatableFormRequest;
@else
use Illuminate\Foundation\Http\FormRequest;
@endif
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

@if($translatable->count() > 0)class Store{{ $modelBaseName }} extends TranslatableFormRequest
@else
class Store{{ $modelBaseName }} extends FormRequest
@endif
{
    use Validated;
    use SanitizesInputs;

    /**
     * {{'@'}}var array{{'<'}}string, array{{'<'}}class-string>>
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $sanitizers = [
        // add your sanitizers for fields
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('admin.{{ $modelDotNotation }}.create');
    }

@if($translatable->count() > 0)/**
     * Get the validation rules that apply to the requests untranslatable fields.
     *
     * {{'@'}}return array{{'<'}}string, string>
     */
    public function untranslatableRules(): array {
        return [
            @foreach($standardColumn as $column)'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverStoreRules']) !!}],
            @endforeach
@if (count($relations))
    @if (count($relations['belongsToMany']))

            @foreach($relations['belongsToMany'] as $belongsToMany)'{{ $belongsToMany['related_table'] }}' => [{!! implode(', ', ['\'array\'']) !!}],
            @endforeach
    @endif
@endif

        ];
    }

    /**
     * Get the validation rules that apply to the requests translatable fields.
     *
     * {{'@'}}return array{{'<'}}string, string|Unique>
     */
    public function translatableRules($locale): array {
        return [
            @foreach($translatableColumns as $column)'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverStoreRules']) !!}],
            @endforeach

        ];
    }
@else
    /**
     * Get the validation rules that apply to the request.
     *
     * {{'@'}}return array{{'<'}}string, string|Unique>
     */
    public function rules(): array
    {
        return [
            @foreach($columns as $column)
@if(!($column['name'] == "updated_by_admin_user_id" || $column['name'] == "created_by_admin_user_id" ))'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverStoreRules']) !!}],
@endif
            @endforeach
@if (count($relations))
    @if (count($relations['belongsToMany']))

            @foreach($relations['belongsToMany'] as $belongsToMany)'{{ $belongsToMany['related_table'] }}' => [{!! implode(', ', ['\'array\'']) !!}],
            @endforeach
    @endif
@endif
        ];
    }
@endif
}
