@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Collection;
    assert($relations instanceof RelationCollection);
    assert($columns instanceof ColumnCollection);
    assert($translatableColumns instanceof ColumnCollection);
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $classNamespace }};
@php
    $uses = new Collection([
        'Illuminate\Container\Container',
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Contracts\Hashing\Hasher',
    ]);
    if($hasPasswordUsage) {
        $uses->push('Illuminate\Validation\Rules\Password');
    }
    if ($hasRuleUsage) {
        $uses->push('Illuminate\Validation\Rule');
    }
    if ($relations->hasBelongsToMany()) {
        $uses->push('Illuminate\Support\Collection');
    }
    if ($translatableColumns->isNotEmpty()) {
        $uses->push('Brackets\Translatable\Http\Requests\TranslatableFormRequest');
    } else {
        $uses->push('Illuminate\Foundation\Http\FormRequest');
    }
    $uses = $uses->unique()->sort();
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

@if($translatableColumns->isNotEmpty())
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

@if($translatableColumns->isNotEmpty())
    /**
     * Get the validation rules that apply to the requests untranslatable fields.
     */
    public function untranslatableRules(): array
    {
        return [
@foreach($columns->getNonTranslatable() as $column)
@if(!($column->name === "updated_by_admin_user_id" || $column->name === "created_by_admin_user_id" ))
            '{{ $column->name }}' => [
@foreach($column->serverStoreRules as $rule)
                {!! (string) $rule !!},
@endforeach
            ],
@endif
@endforeach
@if ($relations->hasBelongsToMany())

@foreach($relations->getBelongsToMany() as $belongsToMany)
            '{{ $belongsToMany->relatedTable }}' => [
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
            '{{ $column->name }}' => [
@foreach($column->serverStoreRules as $rule)
                {!! (string) $rule !!},
@endforeach
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
@if(!($column->name === "updated_by_admin_user_id" || $column->name === "created_by_admin_user_id" ))
            '{{ $column->name }}' => [
@foreach($column->serverStoreRules as $rule)
                {!! (string) $rule !!},
@endforeach
            ],
@endif
@endforeach
@if ($relations->hasBelongsToMany())

@foreach($relations->getBelongsToMany() as $belongsToMany)
            '{{ $belongsToMany->relatedTable }}' => [
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
        $data['{{ $belongsToMany->relatedTable }}'] = new Collection($data['{{ $belongsToMany->relatedTable }}'] ?? []);
@endforeach
@endif

        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }
@if($relations->hasBelongsToMany())
@foreach($relations->getBelongsToMany() as $belongsToMany)

    public function get{{ $belongsToMany->relatedModelName }}Ids(): Collection
    {
        $data = $this->getModifiedData();

        return $data['{{ $belongsToMany->relatedTable }}']->pluck('id');
    }
@endforeach
@endif
}
