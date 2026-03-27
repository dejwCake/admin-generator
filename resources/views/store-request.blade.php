@php use Illuminate\Support\Arr;echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $classNamespace }};
@php
    if($translatable->count() > 0) {
        $translatableColumns = $columns->filter(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
        $standardColumn = $columns->reject(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
    }
    $hasBelongsToMany = count($relations) > 0 && count($relations['belongsToMany']) > 0;
    $uses = [
        'Illuminate\Contracts\Auth\Access\Gate',
    ];
    if ($hasRuleUsage) {
        $uses[] = 'Illuminate\Validation\Rule';
    }
    if ($hasBelongsToMany) {
        $uses[] = 'Illuminate\Support\Collection';
    }
    if ($translatable->count() > 0) {
        $uses[] = 'Brackets\Translatable\Http\Requests\TranslatableFormRequest';
    } else {
        $uses[] = 'Illuminate\Foundation\Http\FormRequest';
    }
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

@if($translatable->count() > 0)
final class {{ $classBaseName }} extends TranslatableFormRequest
@else
final class {{ $classBaseName }} extends FormRequest
@endif
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.{{ $modelDotNotation }}.create');
    }

@if($translatable->count() > 0)
    /**
     * Get the validation rules that apply to the requests untranslatable fields.
     */
    public function untranslatableRules(): array
    {
        return [
@foreach($standardColumn as $column)
@if(!($column['name'] === "updated_by_admin_user_id" || $column['name'] === "created_by_admin_user_id" ))
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverStoreRules']) !!},
            ],
@endif
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [
                'array',
            ],
@endforeach
@endif
        ];
    }

    /**
     * Get the validation rules that apply to the requests translatable fields.
     *
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function translatableRules(string $locale): array
    {
        return [
@foreach($translatableColumns as $column)
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverStoreRules']) !!},
            ],
@endforeach
        ];
    }
@else
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
@foreach($columns as $column)
@if(!($column['name'] === "updated_by_admin_user_id" || $column['name'] === "created_by_admin_user_id" ))
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverStoreRules']) !!},
            ],
@endif
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [
                'array',
            ],
@endforeach
@endif
        ];
    }
@endif

    /**
     * Modify input data
     */
    public function getSanitized(): array
    {
@if($hasBelongsToMany)
        $sanitized = $this->validated();
@foreach($relations['belongsToMany'] as $belongsToMany)
        $sanitized['{{ $belongsToMany['related_table'] }}'] = new Collection($sanitized['{{ $belongsToMany['related_table'] }}'] ?? []);
@endforeach
@else
        //phpcs:ignore SlevomatCodingStandard.Variables.UselessVariable.UselessVariable
        $sanitized = $this->validated();
@endif

        //Add your code for manipulation with request data here

        return $sanitized;
    }
@if($hasBelongsToMany)

@foreach($relations['belongsToMany'] as $belongsToMany)
    public function get{{ $belongsToMany['related_model_name'] }}Ids(): Collection
    {
        $sanitized = $this->getSanitized();

        return $sanitized['{{ $belongsToMany['related_table'] }}']->pluck('id');
    }
@if(!$loop->last)

@endif
@endforeach
@endif
}
