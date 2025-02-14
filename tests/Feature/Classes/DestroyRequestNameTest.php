<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;

class DestroyRequestNameTest extends TestCase
{
    use DatabaseMigrations;

    public function testDestroyRequestGenerationShouldGenerateAnUpdateRequestName(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/DestroyCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DestroyCategory extends FormRequest', File::get($filePath));
    }

    public function testIsGeneratedCorrectNameForCustomModelNameInDestroyRequest(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/DestroyCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
        self::assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\Billing\Cat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DestroyCat extends FormRequest', File::get($filePath));
    }
}
