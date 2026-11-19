<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders\SecurityResetDetector;

use Brackets\AdminGenerator\Builders\SecurityResetDetector;
use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Routing\Router;

class IsUsableTest extends TestCase
{
    public function testFactorsAndTheRoute(): void
    {
        $this->registerResetRoute();

        self::assertTrue($this->app->make(SecurityResetDetector::class)->isUsable('admin_users'));
    }

    public function testFactorsWithoutTheRoute(): void
    {
        self::assertFalse($this->app->make(SecurityResetDetector::class)->isUsable('admin_users'));
    }

    public function testRouteWithoutFactors(): void
    {
        $this->registerResetRoute();

        self::assertFalse($this->app->make(SecurityResetDetector::class)->isUsable('categories'));
    }

    private function registerResetRoute(): void
    {
        $router = $this->app->make(Router::class);
        $router->post('/admin/admin-users/{targetAdminId}/security-reset', static fn (): string => 'ok')
            ->name(SecurityResetDetector::ROUTE);
        $router->getRoutes()->refreshNameLookups();
    }
}
