<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Illuminate\Database\DatabaseManager;
use Illuminate\Routing\Router;

final readonly class SecurityResetDetector
{
    public const string TEMPLATE = 'admin-user';
    public const string ROUTE = 'brackets/admin-auth::admin/admin-users/security-reset';
    public const string TWO_FACTOR_COLUMN = 'two_factor_confirmed_at';
    public const string PASSKEYS_TABLE = 'admin_passkeys';

    public function __construct(private DatabaseManager $databaseManager, private Router $router)
    {
    }

    public function isUsable(?string $template, string $table): bool
    {
        return $template === self::TEMPLATE && $this->hasSecondFactors($table) && $this->hasResetRoute();
    }

    /**
     * Read from the schema, not the generator's column list, which leaves the two-factor columns out.
     */
    private function hasSecondFactors(string $table): bool
    {
        $schemaBuilder = $this->databaseManager->connection()->getSchemaBuilder();

        return $schemaBuilder->hasColumn($table, self::TWO_FACTOR_COLUMN)
            || $schemaBuilder->hasTable(self::PASSKEYS_TABLE);
    }

    private function hasResetRoute(): bool
    {
        return $this->router->has(self::ROUTE);
    }
}
