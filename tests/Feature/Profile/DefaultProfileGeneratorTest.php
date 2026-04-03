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
        $filePathRoute = base_path('routes/admin.php');
        $editPathProfile = resource_path('views/admin/profile/edit-profile.blade.php');
        $formVuePathProfile = resource_path('js/admin/profile-edit-profile/Form.vue');
        $editPathPassword = resource_path('views/admin/profile/edit-password.blade.php');
        $formVuePathPassword = resource_path('js/admin/profile-edit-password/Form.vue');

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
