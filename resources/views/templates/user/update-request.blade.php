@php use Illuminate\Support\Arr;use Illuminate\Support\Collection;echo "<?php";
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
    $uses = [
        'Illuminate\Container\Container',
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Contracts\Hashing\Hasher',
        'Illuminate\Validation\Rules\Password',
        $modelFullName,
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

/**
 * @property {{ $modelBaseName }} ${{ $modelVariableName }}
 */
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
        return $gate->allows('admin.{{ $modelDotNotation }}.edit', $this->{{ $modelVariableName }});
    }

@if($translatable->count() > 0)
    /**
     * Get the validation rules that apply to the requests untranslatable fields.
     */
    public function untranslatableRules(): array
    {
        return [
@foreach($standardColumn as $column)
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverUpdateRules']) !!},
            ],
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [
                'sometimes',
                'array',
            ],
            '{{ $belongsToMany['related_table'] }}.*.id' => [
                'required',
                'integer',
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
                {!! implode(",\n                ", (array) $column['serverUpdateRules']) !!},
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
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverUpdateRules']) !!},
            ],
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [
                'sometimes',
                'array',
            ],
            '{{ $belongsToMany['related_table'] }}.*.id' => [
                'required',
                'integer',
            ],
@endforeach
@endif
        ];
    }
@endif

    /**
     * Modify input data
     */
    public function getModifiedData(): array
    {
        $data = $this->validated();
@if($hasBelongsToMany)
@foreach($relations['belongsToMany'] as $belongsToMany)
        if (isset($data['{{ $belongsToMany['related_table'] }}'])) {
            $data['{{ $belongsToMany['related_table'] }}'] = new Collection($data['{{ $belongsToMany['related_table'] }}'] ?? []);
        }
@endforeach
@endif

        if (array_key_exists('password', $data) && ($data['password'] === '' || $data['password'] === null)) {
            unset($data['password']);
        }
        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }
@if($hasBelongsToMany)

@foreach($relations['belongsToMany'] as $belongsToMany)
    public function get{{ $belongsToMany['related_model_name'] }}Ids(): ?Collection
    {
        $data = $this->getModifiedData();
        if (!isset($data['{{ $belongsToMany['related_table'] }}'])) {
            return null;
        }

        return $data['{{ $belongsToMany['related_table'] }}']->pluck('id');
    }
@if(!$loop->last)

@endif
@endforeach
@endif
}
