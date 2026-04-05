@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $namespace }};
@php
    $uses[] = $modelFullName;
    $uses[] = 'Illuminate\Database\Eloquent\Factories\Attributes\UseModel';
    $uses[] = 'Illuminate\Database\Eloquent\Factories\Factory';
    if ($hasPassword) {
        $uses[] = 'Illuminate\Container\Container';
        $uses[] = 'Illuminate\Contracts\Hashing\Hasher';
    }
    $uses = Arr::sort($uses);
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
@foreach($standardColumns as $col)
            '{{ $col['name'] }}' => {!! $col['faker'] !!},
@endforeach
@foreach($translatableColumns as $col)
            '{{ $col['name'] }}' => ['en' => {!! $col['faker'] !!}],
@endforeach
        ];
    }
@foreach($booleanColumns as $col)

    public function {{ $col['name'] }}(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['{{ $col['name'] }}' => true]);
    }

    public function not{{ Str::ucfirst($col['name']) }}(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['{{ $col['name'] }}' => false]);
    }
@endforeach
@if($hasEmailVerified)

    public function unverified(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['email_verified_at' => null]);
    }
@endif
@if($hasPublishedAt)

    public function notPublished(): self
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        return $this->state(static fn (array $attributes) => ['published_at' => null]);
    }
@endif
}
