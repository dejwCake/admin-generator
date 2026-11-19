<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders\SecurityResetDetector;

use Brackets\AdminGenerator\Builders\SecurityResetDetector;
use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Override;

class HasSecondFactorsTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        $this->app->make(DatabaseManager::class)->connection()->getSchemaBuilder()->dropIfExists('admin_passkeys');

        parent::tearDown();
    }

    public function testTotpColumnsOnly(): void
    {
        self::assertTrue($this->detector()->hasSecondFactors('admin_users'));
    }

    public function testPasskeysTableOnly(): void
    {
        $this->createPasskeysTable();

        self::assertTrue($this->detector()->hasSecondFactors('categories'));
    }

    public function testBoth(): void
    {
        $this->createPasskeysTable();

        self::assertTrue($this->detector()->hasSecondFactors('admin_users'));
    }

    public function testNeither(): void
    {
        self::assertFalse($this->detector()->hasSecondFactors('categories'));
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
