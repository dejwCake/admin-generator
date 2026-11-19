<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders\SecurityResetDetector;

use Brackets\AdminGenerator\Builders\SecurityResetDetector;
use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Router;
use Override;

class IsUsableTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        $this->app->make(DatabaseManager::class)->connection()->getSchemaBuilder()->dropIfExists('admin_passkeys');

        parent::tearDown();
    }

    public function testTotpColumnsAndTheRoute(): void
    {
        $this->registerResetRoute();

        self::assertTrue($this->detector()->isUsable('admin-user', 'admin_users'));
    }

    public function testPasskeysTableAndTheRoute(): void
    {
        $this->registerResetRoute();
        $this->createPasskeysTable();

        self::assertTrue($this->detector()->isUsable('admin-user', 'categories'));
    }

    public function testFactorsWithoutTheRoute(): void
    {
        $this->createPasskeysTable();

        self::assertFalse($this->detector()->isUsable('admin-user', 'admin_users'));
    }

    public function testRouteWithoutFactors(): void
    {
        $this->registerResetRoute();

        self::assertFalse($this->detector()->isUsable('admin-user', 'categories'));
    }

    public function testOtherTemplate(): void
    {
        $this->registerResetRoute();

        self::assertFalse($this->detector()->isUsable('user', 'admin_users'));
    }

    public function testNoTemplate(): void
    {
        $this->registerResetRoute();

        self::assertFalse($this->detector()->isUsable(null, 'admin_users'));
    }

    private function registerResetRoute(): void
    {
        $router = $this->app->make(Router::class);
        $router->post('/admin/admin-users/{targetAdminId}/security-reset', static fn (): string => 'ok')
            ->name(SecurityResetDetector::ROUTE);
        $router->getRoutes()->refreshNameLookups();
    }

    private function createPasskeysTable(): void
    {
        $this->app->make(DatabaseManager::class)->connection()->getSchemaBuilder()->create(
            'admin_passkeys',
            static function (Blueprint $table): void {
                $table->id();
            },
        );
    }

    private function detector(): SecurityResetDetector
    {
        return $this->app->make(SecurityResetDetector::class);
    }
}
