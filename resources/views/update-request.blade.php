@php
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Arr;
    assert($relations instanceof RelationCollection);
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $classNamespace }};
@php
    if ($translatable->count() > 0) {
        $translatableColumns = $columns->filter(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
        $standardColumn = $columns->reject(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
    }
    $uses = [
        'Illuminate\Contracts\Auth\Access\Gate',
        $modelFullName,
    ];
    if ($hasPassword || $hasUpdatedByAdminUser) {
        $uses[] = 'Illuminate\Container\Container';
    }
    if ($hasUpdatedByAdminUser) {
        $uses[] = 'Illuminate\Contracts\Config\Repository as Config';
    }
    if ($hasPassword) {
        $uses[] = 'Illuminate\Contracts\Hashing\Hasher';
    }
    if ($hasPasswordUsage) {
        $uses[] = 'Illuminate\Validation\Rules\Password';
    }
    if ($hasRuleUsage) {
        $uses[] = 'Illuminate\Validation\Rule';
    }
    if ($relations->hasBelongsToMany()) {
        $uses[] = 'Illuminate\Support\Collection';
    }
    if ($translatable->count() > 0) {
        $uses[] = 'Brackets\Translatable\Http\Requests\TranslatableFormRequest';
    } else {
        $uses[] = 'Illuminate\Foundation\Http\FormRequest';
    }
    if ($hasPublishedAt) {
        $uses[] = 'Carbon\CarbonImmutable';
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
@if(!($column['name'] === "updated_by_admin_user_id" || $column['name'] === "created_by_admin_user_id" ))
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverUpdateRules']) !!},
            ],
@endif
@endforeach
@if($hasPublishedAt)

            'publish_now' => [
                'nullable',
                'boolean',
            ],
            'unpublish_now' => [
                'nullable',
                'boolean',
            ],
@endif
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
@if(!($column['name'] === "updated_by_admin_user_id" || $column['name'] === "created_by_admin_user_id" ))
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverUpdateRules']) !!},
            ],
@endif
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
@if($hasPublishedAt)
            'publish_now' => [
                'nullable',
                'boolean',
            ],
            'unpublish_now' => [
                'nullable',
                'boolean',
            ],
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

@if($hasPassword)
        if (array_key_exists('password', $data) && ($data['password'] === '' || $data['password'] === null)) {
            unset($data['password']);
        }
        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

@endif
@if($hasPublishedAt)
        if (isset($data['publish_now']) && $data['publish_now'] === true) {
            $data['published_at'] = CarbonImmutable::now();
        }

        if (isset($data['unpublish_now']) && $data['unpublish_now'] === true) {
            $data['published_at'] = null;
        }

@endif
@if($hasUpdatedByAdminUser)
        $config = Container::getInstance()->make(Config::class);
        assert($config instanceof Config);
        $adminUserGuard = $config->get('admin-auth.defaults.guard', 'admin');
        $data['updated_by_admin_user_id'] = $this->user($adminUserGuard)->id;

@endif
        //Add your code for manipulation with request data here

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
