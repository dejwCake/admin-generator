@php echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $namespace }};

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
@foreach($columns as $col)
@if($col['name'] === 'activated')
            '{{ $col['name'] }}' => true,
@elseif($col['name'] === 'language')
            '{{ $col['name'] }}' => 'en',
@else
            '{{ $col['name'] }}' => {!! $col['faker'] !!},
@endif
@endforeach
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['email_verified_at' => null]);
    }
}
