@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    assert($columns instanceof ColumnCollection)
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $namespace }};
@php
    $uses = new Collection([
        $modelFullName,
        'Illuminate\Database\Eloquent\Factories\Attributes\UseModel',
        'Illuminate\Database\Eloquent\Factories\Factory',
    ]);
    if ($columns->hasByName('password')) {
        $uses->push('Illuminate\Container\Container');
        $uses->push('Illuminate\Contracts\Hashing\Hasher');
    }
    $uses = $uses->unique()->sort();
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

#[UseModel({{ $modelBaseName }}::class)]
final class {{ $modelBaseName }}Factory extends Factory
{
    public function definition(): array
    {
@if($columns->hasByName('password'))
        $hasher = Container::getInstance()->make(Hasher::class);

@endif
        return [
@foreach($columns->getNonTranslatable() as $column)
            '{{ $column->name }}' => {!! $column->faker !!},
@endforeach
@foreach($columns->getTranslatable() as $column)
            '{{ $column->name }}' => ['en' => {!! $column->faker !!}],
@endforeach
        ];
    }
@foreach($columns->getBoolean() as $column)

    public function {{ $column->name }}(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['{{ $column->name }}' => true]);
    }

    public function not{{ Str::ucfirst($column->name) }}(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['{{ $column->name }}' => false]);
    }
@endforeach
@if($columns->hasByName('email_verified_at'))

    public function unverified(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['email_verified_at' => null]);
    }
@endif
@if($columns->hasByName('published_at'))

    public function notPublished(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['published_at' => null]);
    }
@endif
}
