<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class ColumnBuilder
{
    public function __construct(
        private ServerStoreRulesBuilder $serverStoreRulesBuilder,
        private ServerUpdateRulesBuilder $serverUpdateRulesBuilder,
        private FrontendRulesBuilder $frontendRulesBuilder,
    ) {
    }

    public function build(
        string $name,
        string $type,
        bool $nullable,
        string $tableName,
        Collection $indexes,
        bool $hasSoftDelete,
        string $modelVariableName,
    ): Column {
        $hasUniqueIndex = $indexes
            ->contains(static fn (array $index): bool
                => in_array($name, $index['columns'], true) && ($index['unique'] && !$index['primary']));
        $hasUniqueDeleteAtIndex = $indexes
            ->contains(static fn (array $index): bool
                => in_array($name, $index['columns'], true)
                    && ($index['unique'] && !$index['primary'])
                    && str_contains($index['name'], 'deleted_at'));
        // TODO add foreign key

        $majorType = $this->getMajorTypeFromType($type);

        $isForeignKey = str_ends_with($name, '_id')
            && !in_array($name, ['created_by_admin_user_id', 'updated_by_admin_user_id'], true);

        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: $this->getPhpType($majorType),
            faker: $this->getFaker($name, $majorType),
            required: $nullable === false,
            defaultTranslation: $this->getDefaultTranslation($name),
            isForeignKey: $isForeignKey,
            priority: $this->getFixedPriority($name),
            serverStoreRules: $this->serverStoreRulesBuilder->build(
                $name,
                $type,
                $majorType,
                $nullable === false,
                $hasUniqueIndex,
                $tableName,
                $hasSoftDelete && $hasUniqueDeleteAtIndex,
            ),
            serverUpdateRules: $this->serverUpdateRulesBuilder->build(
                $name,
                $type,
                $majorType,
                $nullable === false,
                $hasUniqueIndex,
                $tableName,
                $hasSoftDelete && $hasUniqueDeleteAtIndex,
                $modelVariableName,
            ),
            frontendRules: $this->frontendRulesBuilder->build($name, $majorType, $nullable === false, $isForeignKey),
        );
    }

    private function getMajorTypeFromType(string $type): string
    {
        return match ($type) {
            'datetime',
            'timestamp',
            'timestamptz' => 'datetime',
            'date' => 'date',
            'timetz',
            'time' => 'time',
            'int2',
            'smallint',
            'smallinteger',
            'unsignedsmallint',
            'unsignedsmallinteger',
            'int3',
            'mediumint',
            'mediuminteger',
            'unsignedmediumint',
            'unsignedmediuminteger',
            'int4',
            'int',
            'integer',
            'unsignedinteger',
            'int8',
            'bigint',
            'biginteger',
            'unsignedbigint',
            'unsignedbiginteger' => 'integer',
            'decimal',
            'dec',
            'number',
            'numeric',
            'float',
            'float8',
            'double',
            'real',
            'float4' => 'float',
            'bit',
            'int1',
            'tinyint',
            'tinyinteger',
            'unsignedtinyinteger',
            'bool',
            'boolean' => 'bool',
            'longtext',
            'json',
            'jsonb' => 'json',
            'char',
            'enum',
            'inet4',
            'inet6',
            'tinytext',
            'varchar',
            'uuid',
            'string' => 'string',
            default => 'text',
        };
    }

    private function getPhpType(string $majorType): string
    {
        return match ($majorType) {
            'integer' => 'int',
            'float' => 'float',
            'bool' => 'bool',
            'datetime', 'date' => 'CarbonInterface',
            'json' => 'array',
            default => 'string',
        };
    }

    private function getFaker(string $name, string $majorType): string
    {
        if ($name === 'deleted_at') {
            return 'null';
        }

        if ($name === 'remember_token') {
            return 'null';
        }

        $faker = match ($name) {
            'email' => '$this->faker->email',
            'name',
            'first_name' => '$this->faker->firstName',
            'surname',
            'last_name' => '$this->faker->lastName',
            'slug' => '$this->faker->unique()->slug',
            'password' => '$hasher->make($this->faker->password)',
            'language' => '\'en\'',
            'price' => '$this->faker->randomFloat(2, max: 10000)',
            default => null,
        };

        if ($faker !== null) {
            return $faker;
        }

        return match ($majorType) {
            'date' => '$this->faker->date()',
            'time' => '$this->faker->time()',
            'datetime' => '$this->faker->dateTime',
            'text' => '$this->faker->text()',
            'bool' => '$this->faker->boolean()',
            'integer' => '$this->faker->randomNumber(5)',
            'float' => '$this->faker->randomFloat(2)',
            default => '$this->faker->sentence',
        };
    }

    private function getFixedPriority(string $name): ?int
    {
        return match (true) {
            in_array($name, ['name', 'title', 'last_name', 'subject'], true) => 0,
            in_array($name, ['first_name', 'email', 'author'], true) => 1,
            $name === 'id' => 2,
            $name === 'published_at' => 3,
            default => null,
        };
    }

    private function getDefaultTranslation(string $name): string
    {
        if ($name === 'id') {
            return 'ID';
        }

        if (Str::endsWith(Str::lower($name), '_id')) {
            $name = Str::substr($name, 0, -3);
        }

        return Str::ucfirst(str_replace('_', ' ', $name));
    }
}
