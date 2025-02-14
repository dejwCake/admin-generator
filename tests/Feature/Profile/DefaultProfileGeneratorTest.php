<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Profile;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DefaultProfileGeneratorTest extends UserTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function testAllFilesShouldBeGeneratedUnderDefaultNamespace(): void
    {
        $filePathController = base_path('app/Http/Controllers/Admin/ProfileController.php');
        $filePathRoute = base_path('routes/web.php');
        $editPathProfile = resource_path('views/admin/profile/edit-profile.blade.php');
        $formJsPathProfile = resource_path('js/admin/profile-edit-profile/Form.js');
        $editPathPassword = resource_path('views/admin/profile/edit-password.blade.php');
        $formJsPathPassword = resource_path('js/admin/profile-edit-password/Form.js');
        $indexJsPathPassword = resource_path('js/admin/profile-edit-password/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($filePathController);
        self::assertFileDoesNotExist($editPathProfile);
        self::assertFileDoesNotExist($formJsPathProfile);
        self::assertFileDoesNotExist($editPathPassword);
        self::assertFileDoesNotExist($formJsPathPassword);
        self::assertFileDoesNotExist($indexJsPathPassword);

        $this->artisan('admin:generate:admin-user:profile', [
        ]);

        self::assertFileExists($filePathController);
        self::assertFileExists($editPathProfile);
        self::assertFileExists($formJsPathProfile);
        self::assertFileExists($editPathPassword);
        self::assertFileExists($formJsPathPassword);
        self::assertFileExists($indexJsPathPassword);

        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoute);
        self::assertMatchesFileSnapshot($editPathProfile);
        self::assertMatchesFileSnapshot($formJsPathProfile);
        self::assertMatchesFileSnapshot($editPathPassword);
        self::assertMatchesFileSnapshot($formJsPathPassword);
        self::assertMatchesFileSnapshot($indexJsPathPassword);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }
}
