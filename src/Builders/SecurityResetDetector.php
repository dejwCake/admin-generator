<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Illuminate\Database\DatabaseManager;
use Illuminate\Routing\Router;

/**
 * Whether a project can use admin-auth's security reset: the admins have second factors (TOTP columns
 * or the passkeys table) and the installed admin-auth registers the reset route.
 */
final readonly class SecurityResetDetector
{
    public const string ROUTE = 'brackets/admin-auth::admin/admin-users/security-reset';
    public const string TWO_FACTOR_COLUMN = 'two_factor_confirmed_at';
    public const string PASSKEYS_TABLE = 'admin_passkeys';

    public function __construct(private DatabaseManager $databaseManager, private Router $router)
    {
    }

    public function isUsable(string $table): bool
    {
        return $this->hasSecondFactors($table) && $this->hasResetRoute();
    }

    /**
     * Read from the schema, not the generator's column list, which leaves the two-factor columns out.
     */
    public function hasSecondFactors(string $table): bool
    {
        $schemaBuilder = $this->databaseManager->connection()->getSchemaBuilder();

        return $schemaBuilder->hasColumn($table, self::TWO_FACTOR_COLUMN)
            || $schemaBuilder->hasTable(self::PASSKEYS_TABLE);
    }

    public function hasResetRoute(): bool
    {
        return $this->router->has(self::ROUTE);
    }
}
