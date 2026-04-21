<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateAdminUserProfile;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class DefaultTest extends TestCase
{
    /** @test */
    public function testAllFilesShouldBeGeneratedUnderDefaultNamespace(): void
    {
        $filePathController = $this->app->basePath('app/Http/Controllers/Admin/ProfileController.php');
        $filePathRoute = $this->app->basePath('routes/admin.php');
        $editPathProfile = $this->app->resourcePath('views/admin/profile/edit-profile.blade.php');
        $formVuePathProfile = $this->app->resourcePath('js/admin/profile-edit-profile/Form.vue');
        $editPathPassword = $this->app->resourcePath('views/admin/profile/edit-password.blade.php');
        $formVuePathPassword = $this->app->resourcePath('js/admin/profile-edit-password/Form.vue');

        self::assertFileDoesNotExist($filePathController);
        self::assertFileDoesNotExist($editPathProfile);
        self::assertFileDoesNotExist($formVuePathProfile);
        self::assertFileDoesNotExist($editPathPassword);
        self::assertFileDoesNotExist($formVuePathPassword);

        $this->artisan('admin:generate:admin-user:profile', [
        ]);

        self::assertFileExists($filePathController);
        self::assertFileExists($editPathProfile);
        self::assertFileExists($formVuePathProfile);
        self::assertFileExists($editPathPassword);
        self::assertFileExists($formVuePathPassword);

        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoute);
        self::assertMatchesFileSnapshot($editPathProfile);
        self::assertMatchesFileSnapshot($formVuePathProfile);
        self::assertMatchesFileSnapshot($editPathPassword);
        self::assertMatchesFileSnapshot($formVuePathPassword);
    }
}
