<?php

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;

class StoreRequestNameTest extends TestCase
{
    use DatabaseMigrations;

    public function testStoreRequestGenerationShouldGenerateAStoreRequestName(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/StoreCategory.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:store', [
            'table_name' => 'categories'
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCategory extends FormRequest', File::get($filePath));
    }

    public function testIsGeneratedCorrectNameForCustomModelName(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/StoreCat.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:store', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\Billing\Cat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCat extends FormRequest', File::get($filePath));
    }

    public function testIsGeneratedCorrectNameForCustomModelNameOutsideDefaultFolder(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Cat/StoreCat.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:store', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\Cat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCat extends FormRequest', File::get($filePath));
    }
}
