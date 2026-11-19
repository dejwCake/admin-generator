<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders\SecurityResetDetector;

use Brackets\AdminGenerator\Builders\SecurityResetDetector;
use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Routing\Router;

class HasResetRouteTest extends TestCase
{
    public function testWithoutTheRoute(): void
    {
        self::assertFalse($this->app->make(SecurityResetDetector::class)->hasResetRoute());
    }

    public function testWithTheRoute(): void
    {
        $router = $this->app->make(Router::class);
        $router->post('/admin/admin-users/{targetAdminId}/security-reset', static fn (): string => 'ok')
            ->name(SecurityResetDetector::ROUTE);
        $router->getRoutes()->refreshNameLookups();

        self::assertTrue($this->app->make(SecurityResetDetector::class)->hasResetRoute());
    }
}
