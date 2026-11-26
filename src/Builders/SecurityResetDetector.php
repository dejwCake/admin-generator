<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Illuminate\Database\DatabaseManager;
use Illuminate\Routing\Router;

final readonly class SecurityResetDetector
{
    public const string TEMPLATE = 'admin-user';
    public const string ROUTE = 'brackets/admin-auth::admin/admin-users/security-reset';
    public const string TWO_FACTORS_TABLE = 'admin_two_factors';
    public const string PASSKEYS_TABLE = 'admin_passkeys';

    public function __construct(private DatabaseManager $databaseManager, private Router $router)
    {
    }

    public function isUsable(?string $template): bool
    {
        return $template === self::TEMPLATE && $this->hasSecondFactors() && $this->hasResetRoute();
    }

    /**
     * admin-auth's two-factor or passkeys migration has run.
     */
    private function hasSecondFactors(): bool
    {
        $schemaBuilder = $this->databaseManager->connection()->getSchemaBuilder();

        return $schemaBuilder->hasTable(self::TWO_FACTORS_TABLE) || $schemaBuilder->hasTable(self::PASSKEYS_TABLE);
    }

    private function hasResetRoute(): bool
    {
        return $this->router->has(self::ROUTE);
    }
}
