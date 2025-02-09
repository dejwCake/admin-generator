<?php

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;

class UpdateRequestNameTest extends TestCase
{
    use DatabaseMigrations;

    public function testUpdateRequestGenerationShouldGenerateAnUpdateRequestName(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/UpdateCategory.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories'
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCategory extends FormRequest', File::get($filePath));
    }

    public function testIsGeneratedCorrectNameForCustomModelName(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/UpdateCat.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Http\Requests\Admin\Billing\Cat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCat extends FormRequest', File::get($filePath));
    }
}
