@php use Illuminate\Support\Arr;use Illuminate\Support\Collection;echo "<?php"
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
    $uses = [
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Contracts\Config\Repository as Config',
        'Illuminate\Contracts\Hashing\Hasher',
        'Illuminate\Validation\Rule',
    ];
    if ($translatable->count() > 0) {
        $uses[] = 'Brackets\Translatable\TranslatableFormRequest';
    } else {
        $uses[] = 'Illuminate\Foundation\Http\FormRequest';
    }
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

@if($translatable->count() > 0)
class Update{{ $modelBaseName }} extends TranslatableFormRequest
@else
class Update{{ $modelBaseName }} extends FormRequest
@endif
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate)
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
            '{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [{!! implode(', ', ['\'sometimes\'', '\'array\'']) !!}],
@endforeach
@endif
        ];
    }

    /**
     * Get the validation rules that apply to the requests translatable fields.
     */
    public function translatableRules($locale): array
    {
        return [
@foreach($translatableColumns as $column)
            '{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
@endforeach
        ];
    }
@else
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(Config $config): array
    {
@php
    $columns = (new Collection($columns))->reject(function($column) {
        return $column['name'] == 'activated';
    })->toArray();
@endphp
        $rules = [
@foreach($columns as $column)
            '{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [{!! implode(', ', ['\'sometimes\'', '\'array\'']) !!}],
@endforeach
@endif
        ];

        if ($config->get('admin-auth.activation_enabled')) {
            $rules['activated'] = ['required', 'boolean'];
        }

        return $rules;
    }
@endif

    /**
     * Modify input data
     */
    public function getModifiedData(): array
    {
        $config = app(Config::class);
        assert($config instanceof Config);
        $data = $this->validated();
        if (!$config->get('admin-auth.activation_enabled')) {
            $data['activated'] = true;
        }
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }
        if (!empty($data['password'])) {
            $hasher = app(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }
}
