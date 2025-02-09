<?php

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;

class ModelNameTest extends TestCase
{
    use DatabaseMigrations;

    public function testModelNameShouldAutoGenerateFromTableName(): void
    {
        $filePath = base_path('app/Models/Category.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:model', [
            'table_name' => 'categories'
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model', File::get($filePath));
    }

    public function testYouCanPassCustomClassNameForTheModel(): void
    {
        $filePath = base_path('app/Models/Billing/Category.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:model', [
            'table_name' => 'categories',
            'class_name' => 'Billing\\Category',
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Category extends Model', File::get($filePath));
    }

    public function testClassNameCanBeOutsideDefaultFolder(): void
    {
        $filePath = base_path('app/Billing/Category.php');

        $this->assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:model', [
            'table_name' => 'categories',
            'class_name' => 'App\\Billing\\Category',
        ]);

        $this->assertFileExists($filePath);
        $this->assertStringStartsWith('<?php

namespace App\Billing;

use Illuminate\Database\Eloquent\Model;

class Category extends Model', File::get($filePath));
    }

}
