<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders\SecurityResetDetector;

use Brackets\AdminGenerator\Builders\SecurityResetDetector;
use Brackets\AdminGenerator\Tests\Feature\TestCase;

final class IsUsableTest extends TestCase
{
    public function testTwoFactorTableAndTheRoute(): void
    {
        $this->registerSecurityResetRoute();
        $this->createSecondFactorTable();

        self::assertTrue($this->detector()->isUsable('admin-user'));
    }

    public function testPasskeysTableAndTheRoute(): void
    {
        $this->registerSecurityResetRoute();
        $this->createSecondFactorTable(SecurityResetDetector::PASSKEYS_TABLE);

        self::assertTrue($this->detector()->isUsable('admin-user'));
    }

    public function testFactorsWithoutTheRoute(): void
    {
        $this->createSecondFactorTable(SecurityResetDetector::PASSKEYS_TABLE);

        self::assertFalse($this->detector()->isUsable('admin-user'));
    }

    public function testRouteWithoutFactors(): void
    {
        $this->registerSecurityResetRoute();

        self::assertFalse($this->detector()->isUsable('admin-user'));
    }

    public function testOtherTemplate(): void
    {
        $this->registerSecurityResetRoute();

        self::assertFalse($this->detector()->isUsable('user'));
    }

    public function testNoTemplate(): void
    {
        $this->registerSecurityResetRoute();

        self::assertFalse($this->detector()->isUsable(null));
    }

    private function detector(): SecurityResetDetector
    {
        return $this->app->make(SecurityResetDetector::class);
    }
}
