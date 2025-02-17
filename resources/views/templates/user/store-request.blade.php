@php use Illuminate\Support\Arr;use Illuminate\Support\Collection;echo "<?php";
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
    $hasActivatedColumn = (new Collection($columns))->filter(function($column) {
        return $column['name'] === 'activated';
    })->count() > 0;
    $uses = [
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Contracts\Hashing\Hasher',
        'Illuminate\Validation\Rule',
    ];
    if ($hasActivatedColumn) {
        $uses[] = 'Illuminate\Contracts\Config\Repository as Config';
    }
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
class Store{{ $modelBaseName }} extends TranslatableFormRequest
@else
class Store{{ $modelBaseName }} extends FormRequest
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
            '{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverStoreRules']) !!}],
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [{!! implode(', ', ['\'array\'']) !!}],
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
            '{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverStoreRules']) !!}],
@endforeach
        ];
    }
@else
    /**
     * Get the validation rules that apply to the request.
     */
@if($hasActivatedColumn)
    public function rules(Config $config): array
@else
    public function rules(): array
@endif
    {
@php
    $columns = (new Collection($columns))->reject(function($column) {
        return $column['name'] === 'activated';
    })->toArray();
@endphp
@if($hasActivatedColumn)
        $rules = [
@else
        return [
@endif
@foreach($columns as $column)
            '{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverStoreRules']) !!}],
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)

@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => [{!! implode(', ', ['\'array\'']) !!}],
@endforeach
@endif
        ];
@if($hasActivatedColumn)

        if ($config->get('admin-auth.activation_enabled')) {
            $rules['activated'] = ['required', 'boolean'];
        }

        return $rules;
@endif
    }
@endif

    /**
     * Modify input data
     */
    public function getModifiedData(): array
    {
        $data = $this->validated();
@if($hasActivatedColumn)
        $config = app(Config::class);
        assert($config instanceof Config);
        if (!$config->get('admin-auth.activation_enabled')) {
            $data['activated'] = true;
        }
@endif
        if (isset($data['password'])) {
            $hasher = app(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }
}
