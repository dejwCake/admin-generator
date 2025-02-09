<?php

namespace Brackets\AdminGenerator\Tests\Feature\Appenders;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;

class ModelFactoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testFactoryGeneratorShouldAutoGenerateEverythingFromTable(): void
    {
        $filePath = base_path('database/factories/ModelFactory.php');

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories'
        ]);

        $this->assertStringStartsWith('<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Category::class', File::get($filePath));
    }

    public function testYouCanSpecifyAModelName(): void
    {
        $filePath = base_path('database/factories/ModelFactory.php');

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        $this->assertStringStartsWith('<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Billing\Cat::class', File::get($filePath));
    }

    public function testYouCanSpecifyAModelNameOutsideDefaultFolder(): void
    {
        $filePath = base_path('database/factories/ModelFactory.php');

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\MyCat',
        ]);

        $this->assertStringStartsWith('<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Billing\MyCat::class', File::get($filePath));
    }

}
