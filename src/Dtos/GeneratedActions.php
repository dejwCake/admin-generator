<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos;

use Brackets\AdminGenerator\Enums\GeneratedAction;
use Illuminate\Support\Collection;
use InvalidArgumentException;

use function array_map;
use function explode;
use function implode;
use function sprintf;
use function trim;

final readonly class GeneratedActions
{
    /**
     * @param Collection<int, GeneratedAction> $actions
     */
    private function __construct(private Collection $actions)
    {
    }

    public static function fromOptions(?string $only, ?string $except, bool $withExport, bool $withoutBulk,): self
    {
        if ($only !== null && $except !== null) {
            throw new InvalidArgumentException(
                'The --only and --except options cannot be combined. Pass whichever expresses the '
                . 'resource more directly.',
            );
        }

        $actions = match (true) {
            $only !== null => self::parse($only, '--only'),
            $except !== null => self::all()->reject(
                static fn (GeneratedAction $action): bool => self::parse($except, '--except')
                    ->contains($action),
            ),
            default => self::all()->reject(
                static fn (GeneratedAction $action): bool => $action === GeneratedAction::Export,
            ),
        };

        if ($withExport) {
            $actions = $actions->push(GeneratedAction::Export);
        }

        if ($withoutBulk) {
            $actions = $actions->reject(
                static fn (GeneratedAction $action): bool => $action === GeneratedAction::BulkDelete,
            );
        }

        return new self($actions->unique()->values());
    }

    /**
     * @return Collection<int, GeneratedAction>
     */
    private static function all(): Collection
    {
        /** @var Collection<int, GeneratedAction> $cases */
        $cases = new Collection(GeneratedAction::cases());

        return $cases;
    }

    /**
     * @return Collection<int, GeneratedAction>
     */
    private static function parse(string $value, string $option): Collection
    {
        $names = (new Collection(explode(',', $value)))
            ->map(static fn (string $name): string => trim($name))
            ->filter(static fn (string $name): bool => $name !== '')
            ->values();

        if ($names->isEmpty()) {
            throw new InvalidArgumentException(
                sprintf('%s needs at least one action. Omit it to generate the default set.', $option),
            );
        }

        return $names->map(static function (string $name) use ($option): GeneratedAction {
            $action = GeneratedAction::tryFrom($name);

            if ($action === null) {
                throw new InvalidArgumentException(sprintf(
                    '%s does not know the action "%s". Known actions are %s. `index` is always '
                    . 'generated and cannot be listed.',
                    $option,
                    $name,
                    implode(', ', array_map(
                        static fn (string $known): string => sprintf('`%s`', $known),
                        GeneratedAction::values(),
                    )),
                ));
            }

            return $action;
        });
    }

    public function has(GeneratedAction $action): bool
    {
        return $this->actions->contains($action);
    }

    public function toOptionValue(): string
    {
        return $this->actions
            ->map(static fn (GeneratedAction $action): string => $action->value)
            ->implode(',');
    }
}
