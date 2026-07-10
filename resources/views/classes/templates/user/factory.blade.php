@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Illuminate\Support\Collection;
    assert($columns instanceof ColumnCollection)
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $namespace }};
@php
    // Columns that must never be faked in the default definition:
    // - current_team_id points at a team that the factory does not create
    // - the two_factor_* columns must stay empty unless withTwoFactor() is used
    $skipColumns = ['current_team_id', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'];
    $definitionColumns = $columns->getNonTranslatable()->rejectByName(...$skipColumns);
    $twoFactorColumns = $columns->getNonTranslatable()->filterByName('two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at');
    $hasTwoFactor = $twoFactorColumns->isNotEmpty();

    $uses = new Collection([
        $modelFullName,
        'Illuminate\Database\Eloquent\Factories\Attributes\UseModel',
        'Illuminate\Database\Eloquent\Factories\Factory',
    ]);
    if ($hasPassword) {
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
@if($hasPassword)
        $hasher = Container::getInstance()->make(Hasher::class);

@endif
        return [
@foreach($definitionColumns as $column)
            '{{ $column->name }}' => {!! $column->faker !!},
@endforeach
@foreach($columns->getTranslatable() as $column)
            '{{ $column->name }}' => ['en' => {!! $column->faker !!}],
@endforeach
        ];
    }
@if($hasPassword)

    public function withPassword(string $password): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic, SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes): array => [
            'password' => Container::getInstance()->make(Hasher::class)->make($password),
        ]);
    }
@endif
@if($hasEmailVerifiedAt)

    public function unverified(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic, SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }
@endif
@if($hasTwoFactor)

    public function withTwoFactor(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(fn (array $attributes): array => [
@foreach($twoFactorColumns as $column)
            '{{ $column->name }}' => {!! $column->faker !!},
@endforeach
        ]);
    }
@endif
}
