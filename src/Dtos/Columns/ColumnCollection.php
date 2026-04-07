<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns;

use ArrayIterator;
use Brackets\AdminGenerator\Dtos\Columns\Rules\PasswordRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ServerStoreRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ServerUpdateRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\TimeRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Closure;
use Countable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<string, Column> */
final class ColumnCollection implements IteratorAggregate, Countable
{
    public const array WYSIWYG_COLUMN_NAMES = ['perex', 'text', 'body', 'description'];

    private const array WYSIWYG_COLUMN_MAJOR_TYPES = ['text', 'json'];
    private const array PREFFERED_LABEL_COLUMNS = ['title', 'name', 'first_name', 'email'];

    /** @var Collection<string, Column> */
    private Collection $columns;

    public function __construct(array|Collection $columns = [])
    {
        $collection = $columns instanceof Collection ? $columns : new Collection($columns);

        $this->columns = $collection
            ->filter(static fn ($column) => $column instanceof Column)
            ->keyBy(static fn (Column $column) => $column->name);
    }

    public function push(Column $column): void
    {
        $this->columns->put($column->name, $column);
    }

    public function filter(Closure $callback): self
    {
        $filtered = new self();
        $filtered->columns = $this->columns->filter($callback);

        return $filtered;
    }

    /** @return Collection<int, string|bool> */
    public function pluck(string $property): Collection
    {
        return $this->columns->pluck($property);
    }

    public function count(): int
    {
        return $this->columns->count();
    }

    /** @return array<string, Column> */
    public function toArray(): array
    {
        return $this->columns->all();
    }

    /** @return Traversable<string, Column> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->columns->all());
    }

    public function getVisible(): self
    {
        return new self(
            $this->columns
                ->reject(static fn (Column $column): bool => in_array(
                    $column->name,
                    ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'last_login_at'],
                    true,
                )),
        );
    }

    public function getFillable(): self
    {
        return new self(
            $this->columns
                ->reject(static fn (Column $column): bool => in_array(
                    $column->name,
                    ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'],
                    true,
                )),
        );
    }

    public function getToQuery(): self
    {
        $hasCreatedByAdminUser = $this->hasByName('created_by_admin_user_id');
        $hasUpdatedByAdminUser = $this->hasByName('updated_by_admin_user_id');

        $haystack = ['password', 'remember_token', 'slug', 'created_at', 'updated_at', 'deleted_at'];
        if ($hasCreatedByAdminUser && $hasUpdatedByAdminUser) {
            $haystack = ['password', 'remember_token', 'slug', 'deleted_at'];
        } elseif ($hasCreatedByAdminUser) {
            $haystack = ['password', 'remember_token', 'slug', 'updated_at', 'deleted_at'];
        } elseif ($hasUpdatedByAdminUser) {
            $haystack = ['password', 'remember_token', 'slug', 'created_at', 'deleted_at'];
        }

        return new self($this->columns
            ->reject(
                static fn (Column $column) => $column->majorType === 'text' || in_array($column->name, $haystack, true),
            ));
    }

    public function getToSearchIn(): self
    {
        return new self(
            $this->columns->filter(
                static fn (Column $column): bool =>
                    in_array($column->majorType, ['json', 'text', 'string'], true) || $column->name === 'id',
            )->filter(
                static fn (Column $column) => !in_array($column->name, ['password', 'remember_token'], true),
            ),
        );
    }

    public function getToExport(): self
    {
        return new self(
            $this->columns->filter(
                static fn (Column $column): bool => !in_array(
                    $column->name,
                    ['password', 'remember_token', 'updated_at', 'created_at', 'deleted_at'],
                    true,
                ),
            ),
        );
    }

    public function getForIndex(): self
    {
        return new self(
            $this->columns->filter(
                static fn (Column $column): bool => $column->priority !== null,
            ),
        );
    }

    public function getTranslatable(): self
    {
        return new self(
            $this->columns->filter(
                static fn (Column $column) => $column->majorType === 'json',
            ),
        );
    }

    public function getNonTranslatable(): self
    {
        return new self(
            $this->columns->reject(
                static fn (Column $column) => $column->majorType === 'json' || $column->name === 'id',
            ),
        );
    }

    public function getBoolean(): self
    {
        return new self(
            $this->columns->filter(static fn (Column $column) => $column->majorType === 'bool'),
        );
    }

    public function getDates(): self
    {
        return new self(
            $this->columns->filter(
                static fn (Column $column) => in_array($column->majorType, ['datetime', 'date'], true),
            ),
        );
    }

    public function getHidden(): self
    {
        return new self(
            $this->columns->filter(
                static fn (Column $column) => in_array($column->name, ['password', 'remember_token'], true),
            ),
        );
    }

    public function filterByName(string ...$names): self
    {
        return new self(
            $this->columns->filter(
                static fn (Column $column) => in_array($column->name, $names, true),
            ),
        );
    }

    public function rejectByName(string ...$names): self
    {
        return new self(
            $this->columns->reject(
                static fn (Column $column) => in_array($column->name, $names, true),
            ),
        );
    }

    /** @return array<int, string> */
    public function getWysiwygColumnNames(): array
    {
        return self::WYSIWYG_COLUMN_NAMES;
    }

    public function hasByName(string ...$names): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => in_array($column->name, $names, true),
        );
    }

    public function hasByMajorType(string ...$majorTypes): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => in_array($column->majorType, $majorTypes, true),
        );
    }

    public function isNotEmpty(): bool
    {
        return $this->columns->isNotEmpty();
    }

    public function hasWysiwyg(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool =>
                in_array($column->majorType, self::WYSIWYG_COLUMN_MAJOR_TYPES, true)
                    && in_array($column->name, self::WYSIWYG_COLUMN_NAMES, true),
        );
    }

    public function hasTextarea(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => $column->majorType === 'text'
                && !in_array($column->name, self::WYSIWYG_COLUMN_NAMES, true),
        );
    }

    public function hasLocalizedInput(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => $column->majorType === 'json'
                && !in_array($column->name, self::WYSIWYG_COLUMN_NAMES, true),
        );
    }

    public function hasLocalizedWysiwyg(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => $column->majorType === 'json'
                && in_array($column->name, self::WYSIWYG_COLUMN_NAMES, true),
        );
    }

    public function hasFormInput(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => !in_array($column->name, ['password', 'email'], true)
                && !in_array($column->majorType, ['json', 'text', 'bool', 'date', 'time', 'datetime'], true)
                && !$column->isForeignKey,
        );
    }

    public function hasStoreRuleUsage(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => $column->serverStoreRules
                ->contains(
                    static fn (ServerStoreRule $rule): bool => $rule instanceof TimeRule || $rule instanceof UniqueRule,
                ),
        );
    }

    public function hasStorePasswordUsage(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => $column->serverStoreRules
                ->contains(
                    static fn (ServerStoreRule $rule): bool => $rule instanceof PasswordRule,
                ),
        );
    }

    public function hasUpdateRuleUsage(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => $column->serverUpdateRules
                ->contains(
                    static fn (ServerUpdateRule $rule): bool => $rule instanceof TimeRule
                        || $rule instanceof UniqueRule,
                ),
        );
    }

    public function hasUpdatePasswordUsage(): bool
    {
        return $this->columns->contains(
            static fn (Column $column): bool => $column->serverUpdateRules
                ->contains(
                    static fn (ServerUpdateRule $rule): bool => $rule instanceof PasswordRule,
                ),
        );
    }

    /** @return array<string, string> */
    public function getFrontendValidationRules(): array
    {
        return $this->columns
            ->filter(static fn (Column $column): bool => $column->getFrontendValidationRule() !== null)
            ->mapWithKeys(static fn (Column $column): array => [$column->name => $column->getFrontendValidationRule()])
            ->all();
    }

    public function getLabelColumn(): string
    {
        foreach (self::PREFFERED_LABEL_COLUMNS as $label) {
            if ($this->columns->has($label)) {
                return $label;
            }
        }

        $firstString = $this->columns->first(
            static fn (Column $column): bool => $column->majorType === 'string',
        );

        return $firstString->name ?? 'id';
    }
}
