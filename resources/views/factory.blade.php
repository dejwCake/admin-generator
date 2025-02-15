@php echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $namespace }};
@php
    $translatableColumns = $columns->filter(function($column) use ($translatable) {
        return in_array($column['name'], $translatable->toArray());
    });
    $standardColumn = $columns->reject(function($column) use ($translatable) {
        return in_array($column['name'], $translatable->toArray());
    });
@endphp

use {{ $modelFullName }};
use Illuminate\Database\Eloquent\Factories\Factory;

class {{ $modelBaseName }}Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $model = {{ $modelBaseName }}::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
@foreach($standardColumn as $col)
            '{{ $col['name'] }}' => {!! $col['faker'] !!},
@endforeach
@foreach($translatableColumns as $col)
            '{{ $col['name'] }}' => ['en' => {!! $col['faker'] !!}],
@endforeach
        ];
    }
}
