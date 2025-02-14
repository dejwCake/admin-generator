<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Profile;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;

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
        self::assertStringStartsWith('<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{', File::get($filePath));
    }
}
