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
    $uses = [
        'Illuminate\Contracts\Auth\Access\Gate',
    ];
    if ($hasPassword || $hasCreatedByAdminUserId || $hasUpdatedByAdminUserId) {
        $uses[] = 'Illuminate\Container\Container';
    }
    if ($hasCreatedByAdminUserId || $hasUpdatedByAdminUserId) {
        $uses[] = 'Illuminate\Contracts\Config\Repository as Config';
    }
    if ($hasPassword) {
        $uses[] = 'Illuminate\Contracts\Hashing\Hasher';
        $uses[] = 'Illuminate\Validation\Rules\Password';
    }
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
@if($hasBelongsToMany)
        $data = $this->validated();
@foreach($relations['belongsToMany'] as $belongsToMany)
        $data['{{ $belongsToMany['related_table'] }}'] = new Collection($data['{{ $belongsToMany['related_table'] }}'] ?? []);
@endforeach
@elseif(!$hasPassword && !$hasCreatedByAdminUserId && !$hasUpdatedByAdminUserId)
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
@if($hasCreatedByAdminUserId || $hasUpdatedByAdminUserId)
        $config = Container::getInstance()->make(Config::class);
        assert($config instanceof Config);
        $adminUserGuard = $config->get('admin-auth.defaults.guard', 'admin');
@if($hasCreatedByAdminUserId)
        $data['created_by_admin_user_id'] = $this->user($adminUserGuard)->id;
@endif
@if($hasUpdatedByAdminUserId)
        $data['updated_by_admin_user_id'] = $this->user($adminUserGuard)->id;
@endif

@endif
        //Add your code for manipulation with request data here

        return $data;
    }
@if($hasBelongsToMany)
@foreach($relations['belongsToMany'] as $belongsToMany)

    public function get{{ $belongsToMany['related_model_name'] }}Ids(): Collection
    {
        $data = $this->getModifiedData();

        return $data['{{ $belongsToMany['related_table'] }}']->pluck('id');
    }
@endforeach
@endif
}
