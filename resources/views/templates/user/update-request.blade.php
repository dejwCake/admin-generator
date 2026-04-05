@php
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Collection;
    assert($relations instanceof RelationCollection);
@endphp
@php echo "<?php";
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
    $uses = new Collection([
        'Illuminate\Container\Container',
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Contracts\Hashing\Hasher',
        $modelFullName,
    ]);
    if ($hasPasswordUsage) {
        $uses->push('Illuminate\Validation\Rules\Password');
    }
    if ($hasRuleUsage) {
        $uses->push('Illuminate\Validation\Rule');
    }
    if ($relations->hasBelongsToMany()) {
        $uses->push('Illuminate\Support\Collection');
    }
    if ($translatable->count() > 0) {
        $uses->push('Brackets\Translatable\Http\Requests\TranslatableFormRequest');
    } else {
        $uses->push('Illuminate\Foundation\Http\FormRequest');
    }
    $uses = $uses->unique()->sort();
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
@if ($relations->hasBelongsToMany())

@foreach($relations->getBelongsToMany() as $belongsToMany)
            '{{ $belongsToMany->relatedTable }}' => [
                'sometimes',
                'array',
            ],
            '{{ $belongsToMany->relatedTable }}.*.id' => [
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
@if ($relations->hasBelongsToMany())

@foreach($relations->getBelongsToMany() as $belongsToMany)
            '{{ $belongsToMany->relatedTable }}' => [
                'sometimes',
                'array',
            ],
            '{{ $belongsToMany->relatedTable }}.*.id' => [
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
@if($relations->hasBelongsToMany())
@foreach($relations->getBelongsToMany() as $belongsToMany)
        if (isset($data['{{ $belongsToMany->relatedTable }}'])) {
            $data['{{ $belongsToMany->relatedTable }}'] = new Collection($data['{{ $belongsToMany->relatedTable }}'] ?? []);
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
@if($relations->hasBelongsToMany())

@foreach($relations->getBelongsToMany() as $belongsToMany)
    public function get{{ $belongsToMany->relatedModelName }}Ids(): ?Collection
    {
        $data = $this->getModifiedData();
        if (!isset($data['{{ $belongsToMany->relatedTable }}'])) {
            return null;
        }

        return $data['{{ $belongsToMany->relatedTable }}']->pluck('id');
    }
@if(!$loop->last)

@endif
@endforeach
@endif
}
