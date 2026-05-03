<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Filesystem\Filesystem;

final class ResourceGeneratorTest extends TestCase
{
    public function testRegistrationInsertsAndSortsInsideMarkers(): void
    {
        $adminJsPath = $this->app->resourcePath('js/admin/admin.js');

        $this->artisan('admin:generate:vue-listing', ['table_name' => 'posts']);
        $this->artisan('admin:generate:vue-form', ['table_name' => 'categories']);
        $this->artisan('admin:generate:vue-listing', ['table_name' => 'categories']);
        $this->artisan('admin:generate:vue-form', ['table_name' => 'posts']);

        self::assertMatchesFileSnapshot($adminJsPath);
    }

    public function testRegistrationSortsExistingUnorderedEntries(): void
    {
        $adminJsPath = $this->app->resourcePath('js/admin/admin.js');
        $filesystem = $this->app->make(Filesystem::class);

        $seeded = <<<'JS'
            import './bootstrap';
            import { createApp } from 'vue';

            //-- Do not delete me :) I'm used for auto-generation js import begin --
            import PostListing from './post/Listing.vue';
            import AdminUserForm from './admin-user/Form.vue';
            //-- Do not delete me :) I'm used for auto-generation js import end --

            const app = createApp({});

            //-- Do not delete me :) I'm used for auto-generation component registration begin --
            app.component('PostListing', PostListing);
            app.component('AdminUserForm', AdminUserForm);
            //-- Do not delete me :) I'm used for auto-generation component registration end --

            app.mount('#app');
            JS;
        $filesystem->put($adminJsPath, $seeded);

        $this->artisan('admin:generate:vue-listing', ['table_name' => 'categories']);

        self::assertMatchesFileSnapshot($adminJsPath);
    }

    public function testRegistrationIsIdempotent(): void
    {
        $adminJsPath = $this->app->resourcePath('js/admin/admin.js');

        $this->artisan('admin:generate:vue-listing', ['table_name' => 'posts']);
        $afterFirst = $this->app->make(Filesystem::class)->get($adminJsPath);

        $this->artisan('admin:generate:vue-listing', ['table_name' => 'posts', '--force' => true]);
        $afterSecond = $this->app->make(Filesystem::class)->get($adminJsPath);

        self::assertSame($afterFirst, $afterSecond);
    }
}
