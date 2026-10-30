<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\DestroyCategory;
use App\Http\Requests\Admin\Category\IndexCategory;
use App\Models\Category;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final class CategoriesController extends Controller
{
    public function __construct(
        private readonly Gate $gate,
        private readonly Redirector $redirector,
        private readonly UrlGenerator $urlGenerator,
        private readonly ViewFactory $viewFactory,
        private readonly ListingBuilder $listingBuilder,
        private readonly ListingQueryBuilder $listingQueryBuilder,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexCategory $request): array|View
    {
        $trashed = $request->boolean('trashed');

        $data = $this->listingBuilder->for(Category::class)
            ->build()
            ->processRequestAndGet(
                $this->listingQueryBuilder->fromRequest(
                    $request,
                    [
                        'id',
                        'uuid',
                        'user_id',
                        'title',
                        'name',
                        'first_name',
                        'last_name',
                        'subject',
                        'email',
                        'language',
                        'published_at',
                        'published_to',
                        'date_start',
                        'time_start',
                        'date_time_end',
                        'released_at',
                        'text',
                        'description',
                        'enabled',
                        'send',
                        'price',
                        'rating',
                        'views',
                        'created_by_admin_user_id',
                        'updated_by_admin_user_id',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    [
                        'id',
                        'uuid',
                        'title',
                        'name',
                        'first_name',
                        'last_name',
                        'subject',
                        'email',
                        'language',
                        'slug',
                        'perex',
                        'long_text',
                        'text',
                        'description',
                    ],
                ),
                function (Builder $query) use ($trashed): void {
                    $query->with([
                        'createdByAdminUser',
                        'updatedByAdminUser',
                        'user',
                    ]);

                    if ($trashed) {
                        $this->onlyTrashed($query);
                    }
                },
            );

        if ($trashed) {
            $data->each(
                static fn (Category $category): Category => $category
                    ->setAttribute('restorable', $category->isRestorable()),
            );
        }

        if ($request->ajax()) {
            return [
                'data' => $data,
            ];
        }

        return $this->viewFactory->make(
            'admin.category.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/categories/index'),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/categories/destroy', ['category' => ':id']),
                'forceDestroyUrlTemplate' => $this->urlGenerator->route(
                    'admin/categories/force-destroy',
                    ['category' => ':id'],
                ),
                'restoreUrlTemplate' => $this->urlGenerator->route('admin/categories/restore', ['category' => ':id']),
            ],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(
        DestroyCategory $request,
        Category $category,
        DatabaseManager $databaseManager,
    ): array|RedirectResponse {
        $databaseManager->transaction(static fn (): ?bool => $category->delete());

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }

    /**
     * @param Builder<Category> $query
     */
    private function onlyTrashed(Builder $query): void
    {
        $query->onlyTrashed();
    }
}
