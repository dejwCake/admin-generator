<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Users;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;

class UserCrudGeneratorWithCustomModelNameTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testAllFilesShouldBeGeneratedWithCustomModel(): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/Auth/UsersController.php');
        $storePath = base_path('app/Http/Requests/Admin/User/StoreUser.php');
        $updatePath = base_path('app/Http/Requests/Admin/User/UpdateUser.php');
        $routesPath = base_path('routes/web.php');
        $indexPath = resource_path('views/admin/user/index.blade.php');
        $indexJsPath = resource_path('js/admin/user/Listing.js');
        $elementsPath = resource_path('views/admin/user/components/form-elements.blade.php');
        $createPath = resource_path('views/admin/user/create.blade.php');
        $editPath = resource_path('views/admin/user/edit.blade.php');
        $formJsPath = resource_path('js/admin/user/Form.js');
        $factoryPath = base_path('database/factories/ModelFactory.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($storePath);
        self::assertFileDoesNotExist($updatePath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($elementsPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);


        $this->artisan('admin:generate:user', [
            '--controller-name' => 'Auth\\UsersController',
            '--model-name' => 'App\\User',
        ]);

        self::assertFileExists($controllerPath);
        self::assertFileExists($storePath);
        self::assertFileExists($updatePath);
        self::assertFileExists($indexPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($elementsPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($storePath);
        self::assertMatchesFileSnapshot($updatePath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($elementsPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($factoryPath);

        self::assertStringStartsWith('<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\DestroyUser;
use App\Http\Requests\Admin\User\IndexUser;
use App\Http\Requests\Admin\User\StoreUser;
use App\Http\Requests\Admin\User\UpdateUser;
use App\User;
use Spatie\Permission\Models\Role;
use Brackets\AdminListing\Facades\AdminListing;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class UsersController extends Controller', File::get($controllerPath));
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StoreUser extends FormRequest
{', File::get($storePath));
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateUser extends FormRequest
{', File::get($updatePath));
        self::assertStringStartsWith(
            '<?php



/* Auto-generated admin routes */
Route::middleware([\'auth:\' . config(\'admin-auth.defaults.guard\'), \'admin\'])->group(static function () {
    Route::prefix(\'admin\')->namespace(\'App\Http\Controllers\Admin\')->name(\'admin/\')->group(static function() {
        Route::prefix(\'users\')->name(\'users/\')->group(static function() {
            Route::get(\'/\',                                             \'Auth\UsersController@index\')->name(\'index\');
            Route::get(\'/create\',                                       \'Auth\UsersController@create\')->name(\'create\');
            Route::post(\'/\',                                            \'Auth\UsersController@store\')->name(\'store\');
            Route::get(\'/{user}/edit\',                                  \'Auth\UsersController@edit\')->name(\'edit\');
            Route::post(\'/{user}\',                                      \'Auth\UsersController@update\')->name(\'update\');
            Route::delete(\'/{user}\',                                    \'Auth\UsersController@destroy\')->name(\'destroy\');
            Route::get(\'/{user}/resend-activation\',                     \'Auth\UsersController@resendActivationEmail\')->name(\'resendActivationEmail\');
        });
    });
});',
            File::get($routesPath),
        );
        self::assertStringStartsWith('@extends(\'brackets/admin-ui::admin.layout.default\')', File::get($indexPath));
        self::assertStringStartsWith('import AppListing from \'../app-components/Listing/AppListing\';

Vue.component(\'user-listing\'', File::get($indexJsPath));
        self::assertStringStartsWith('<div ', File::get($elementsPath));
        self::assertStringStartsWith('@extends(\'brackets/admin-ui::admin.layout.default\')', File::get($createPath));
        self::assertStringStartsWith('@extends(\'brackets/admin-ui::admin.layout.default\')', File::get($editPath));
        self::assertStringStartsWith('import AppForm from \'../app-components/Form/AppForm\';

Vue.component(\'user-form\'', File::get($formJsPath));
        self::assertStringStartsWith('<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class', File::get($factoryPath));
    }

    public function testUserFactoryGeneratorShouldGenerateEverythingWithCustomModelName(): void
    {
        $filePath = base_path('database/factories/ModelFactory.php');

        $this->artisan('admin:generate:user', [
            '--model-name' => 'Auth\\User',
        ]);

        self::assertMatchesFileSnapshot($filePath);
        self::assertStringStartsWith('<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Auth\User::class', File::get($filePath));
    }
}
