@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Illuminate\Support\Collection;
    assert($columns instanceof ColumnCollection);
    assert($definitionColumns instanceof ColumnCollection);
    assert($twoFactorColumns instanceof ColumnCollection);
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $namespace }};
@php
    $uses = new Collection([
        $modelFullName,
        'DateTime',
        'Illuminate\Database\Eloquent\Factories\Attributes\UseModel',
        'Illuminate\Database\Eloquent\Factories\Factory',
    ]);
    if ($hasPassword) {
        $uses->push('Illuminate\Container\Container');
        $uses->push('Illuminate\Contracts\Hashing\Hasher');
    }
    if ($hasTwoFactor) {
        $uses->push('Carbon\CarbonImmutable');
        $uses->push('Illuminate\Container\Container');
        $uses->push('Illuminate\Contracts\Encryption\Encrypter');
    }
    $uses = $uses->unique()->sort();
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

/**
 * @@extends Factory<{{ $modelBaseName }}>
 */
#[UseModel({{ $modelBaseName }}::class)]
final class {{ $modelBaseName }}Factory extends Factory
{
    /**
     * @return array<string, DateTime|string|null>
     */
    public function definition(): array
    {
@if($hasPassword)
        $hasher = Container::getInstance()->make(Hasher::class);

@endif
        return [
@foreach($definitionColumns as $column)
@if($twoFactorColumns->hasByName($column->name))
            '{{ $column->name }}' => null,
@else
            '{{ $column->name }}' => {!! $column->faker !!},
@endif
@endforeach
@foreach($columns->getTranslatable() as $column)
            '{{ $column->name }}' => ['en' => {!! $column->faker !!}],
@endforeach
        ];
    }
@if($hasEmailVerifiedAt)

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter,SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }
@endif
@if($hasTwoFactor)

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        $encrypter = Container::getInstance()->make(Encrypter::class);

        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter,SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => $encrypter->encrypt('secret'),
            'two_factor_recovery_codes' => $encrypter->encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => CarbonImmutable::now(),
        ]);
    }
@endif
@if($hasPassword)

    /**
     * Indicate that the user has a known password (defaults to "password").
     */
    public function withPassword(string $password = 'password'): static
    {
        $hasher = Container::getInstance()->make(Hasher::class);

        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter,SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        return $this->state(fn (array $attributes) => [
            'password' => $hasher->make($password),
        ]);
    }
@endif
}
