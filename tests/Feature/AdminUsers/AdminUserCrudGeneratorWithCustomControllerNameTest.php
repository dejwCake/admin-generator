<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\AdminUsers;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminUserCrudGeneratorWithCustomControllerNameTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testAdminUserControllerNameCanBeNamespaced(): void
    {
        $filePathController = base_path('app/Http/Controllers/Admin/Auth/AdminUsersController.php');
        $filePathRoutes = base_path('routes/web.php');

        self::assertFileDoesNotExist($filePathController);

        $this->artisan('admin:generate:admin-user', [
            '--controller-name' => 'Auth\\AdminUsersController',
        ]);

        self::assertFileExists($filePathController);
        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoutes);
    }

    public function testAdminUserControllerNameCanBeOutsideDefaultDirectory(): void
    {
        $filePath = base_path('app/Http/Controllers/Auth/AdminUsersController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:admin-user', [
            '--controller-name' => 'App\\Http\\Controllers\\Auth\\AdminUsersController',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
//        self::assertStringStartsWith('<?php
//
//namespace App\Http\Controllers\Auth;
//
//use App\Http\Controllers\Controller;
//use App\Http\Requests\Admin\AdminUser\DestroyAdminUser;
//use App\Http\Requests\Admin\AdminUser\ImpersonalLoginAdminUser;
//use App\Http\Requests\Admin\AdminUser\IndexAdminUser;
//use App\Http\Requests\Admin\AdminUser\StoreAdminUser;
//use App\Http\Requests\Admin\AdminUser\UpdateAdminUser;
//use Brackets\AdminAuth\Models\AdminUser;
//use Spatie\Permission\Models\Role;
//use Brackets\AdminAuth\Activation\Facades\Activation;
//use Brackets\AdminAuth\Services\ActivationService;
//use Brackets\AdminListing\Facades\AdminListing;
//use Exception;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Auth\Access\AuthorizationException;
//use Illuminate\Contracts\Routing\ResponseFactory;
//use Illuminate\Contracts\View\Factory;
//use Illuminate\Http\RedirectResponse;
//use Illuminate\Http\Request;
//use Illuminate\Http\Response;
//use Illuminate\Routing\Redirector;
//use Illuminate\Support\Facades\Config;
//use Illuminate\View\View;
//
//class AdminUsersController extends Controller', File::get($filePath));
    }
}
