<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Profile;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProfileGeneratorWithCustomModelNameTest extends UserTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function testProfileControllerShouldBeGeneratedWithCustomModel(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/Auth/ProfileController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:admin-user:profile', [
            '--controller-name' => 'Auth\\ProfileController',
            '--model-name' => 'App\\User',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
