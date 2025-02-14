<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\AdminUsers;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;

class DefaultAdminUserCrudGeneratorTest extends UserTestCase
{
    use DatabaseMigrations;

    #[DataProvider('getCases')]
    public function testAllFilesShouldBeGeneratedUnderDefaultNamespace(bool $seed): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/AdminUsersController.php');
        $indexRequestPath = base_path('app/Http/Requests/Admin/AdminUser/IndexAdminUser.php');
        $storePath = base_path('app/Http/Requests/Admin/AdminUser/StoreAdminUser.php');
        $updatePath = base_path('app/Http/Requests/Admin/AdminUser/UpdateAdminUser.php');
        $destroyPath = base_path('app/Http/Requests/Admin/AdminUser/DestroyAdminUser.php');
        $routesPath = base_path('routes/web.php');
        $indexPath = resource_path('views/admin/admin-user/index.blade.php');
        $listingJsPath = resource_path('js/admin/admin-user/Listing.js');
        $indexJsPath = resource_path('js/admin/admin-user/index.js');
        $elementsPath = resource_path('views/admin/admin-user/components/form-elements.blade.php');
        $createPath = resource_path('views/admin/admin-user/create.blade.php');
        $editPath = resource_path('views/admin/admin-user/edit.blade.php');
        $formJsPath = resource_path('js/admin/admin-user/Form.js');
        $factoryPath = base_path('database/factories/ModelFactory.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($indexRequestPath);
        self::assertFileDoesNotExist($storePath);
        self::assertFileDoesNotExist($updatePath);
        self::assertFileDoesNotExist($destroyPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($elementsPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);


        if ($seed) {
            $this->artisan('admin:generate:admin-user', ['--seed' => true]);
        } else {
            $this->artisan('admin:generate:admin-user');
        }

        self::assertFileExists($controllerPath);
        self::assertFileExists($indexRequestPath);
        self::assertFileExists($storePath);
        self::assertFileExists($updatePath);
        self::assertFileExists($destroyPath);
        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($elementsPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);

//        self::assertStringStartsWith('<?php
//
//namespace App\Http\Controllers\Admin;
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
//class AdminUsersController extends Controller', File::get($controllerPath));
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\AdminUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class IndexAdminUser extends FormRequest
{', File::get($indexRequestPath));
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\AdminUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StoreAdminUser extends FormRequest
{', File::get($storePath));
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\AdminUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateAdminUser extends FormRequest
{', File::get($updatePath));
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\AdminUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DestroyAdminUser extends FormRequest
{', File::get($destroyPath));
        self::assertStringStartsWith(
            '<?php



/* Auto-generated admin routes */
Route::middleware([\'auth:\' . config(\'admin-auth.defaults.guard\'), \'admin\'])->group(static function () {
    Route::prefix(\'admin\')->namespace(\'App\Http\Controllers\Admin\')->name(\'admin/\')->group(static function() {
        Route::prefix(\'admin-users\')->name(\'admin-users/\')->group(static function() {
            Route::get(\'/\',                                             \'AdminUsersController@index\')->name(\'index\');
            Route::get(\'/create\',                                       \'AdminUsersController@create\')->name(\'create\');
            Route::post(\'/\',                                            \'AdminUsersController@store\')->name(\'store\');
            Route::get(\'/{adminUser}/impersonal-login\',                 \'AdminUsersController@impersonalLogin\')->name(\'impersonal-login\');
            Route::get(\'/{adminUser}/edit\',                             \'AdminUsersController@edit\')->name(\'edit\');
            Route::post(\'/{adminUser}\',                                 \'AdminUsersController@update\')->name(\'update\');
            Route::delete(\'/{adminUser}\',                               \'AdminUsersController@destroy\')->name(\'destroy\');
            Route::get(\'/{adminUser}/resend-activation\',                \'AdminUsersController@resendActivationEmail\')->name(\'resendActivationEmail\');
        });
    });
});',
            File::get($routesPath),
        );
        self::assertStringStartsWith('@extends(\'brackets/admin-ui::admin.layout.default\')', File::get($indexPath));
        self::assertStringStartsWith('import AppListing from \'../app-components/Listing/AppListing\';

Vue.component(\'admin-user-listing\'', File::get($listingJsPath));
        self::assertStringStartsWith('<div ', File::get($elementsPath));
        self::assertStringStartsWith('@extends(\'brackets/admin-ui::admin.layout.default\')', File::get($createPath));
        self::assertStringStartsWith('@extends(\'brackets/admin-ui::admin.layout.default\')', File::get($editPath));
        self::assertStringStartsWith('import AppForm from \'../app-components/Form/AppForm\';

Vue.component(\'admin-user-form\'', File::get($formJsPath));
        self::assertStringStartsWith('import \'./Listing\';', File::get($indexJsPath));
        self::assertStringStartsWith('<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Brackets\AdminAuth\Models\AdminUser::class', File::get($factoryPath));

        if ($seed) {
            self::assertDatabaseCount('admin_users', 20);
        }
    }

    public static function getCases(): iterable
    {
        yield 'without seed' => [false];

        //disabled for now, because it's not working properly
        //yield 'with seed' => [true];
    }
}
