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
        'Illuminate\Contracts\Auth\Access\Gate',
    ]);
    if ($hasPassword || $hasCreatedByAdminUser || $hasUpdatedByAdminUser) {
        $uses->push('Illuminate\Container\Container');
    }
    if ($hasCreatedByAdminUser || $hasUpdatedByAdminUser) {
        $uses->push('Illuminate\Contracts\Config\Repository as Config');
    }
    if ($hasPassword) {
        $uses->push('Illuminate\Contracts\Hashing\Hasher');
    }
    if($hasPasswordUsage) {
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
@if($relations->hasBelongsToMany())
        $data = $this->validated();
@foreach($relations->getBelongsToMany() as $belongsToMany)
        $data['{{ $belongsToMany->relatedTable }}'] = new Collection($data['{{ $belongsToMany->relatedTable }}'] ?? []);
@endforeach
@elseif(!$hasPassword && !$hasCreatedByAdminUser && !$hasUpdatedByAdminUser)
        //phpcs:ignore SlevomatCodingStandard.Variables.UselessVariable.UselessVariable
        $data = $this->validated();
@else
        $data = $this->validated();
@endif

@if($hasPassword)
        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

@endif
@if($hasCreatedByAdminUser || $hasUpdatedByAdminUser)
        $config = Container::getInstance()->make(Config::class);
        assert($config instanceof Config);
        $adminUserGuard = $config->get('admin-auth.defaults.guard', 'admin');
@if($hasCreatedByAdminUser)
        $data['created_by_admin_user_id'] = $this->user($adminUserGuard)->id;
@endif
@if($hasUpdatedByAdminUser)
        $data['updated_by_admin_user_id'] = $this->user($adminUserGuard)->id;
@endif

@endif
        //Add your code for manipulation with request data here

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
