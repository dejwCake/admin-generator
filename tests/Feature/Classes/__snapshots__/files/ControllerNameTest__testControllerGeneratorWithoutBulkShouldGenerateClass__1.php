<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\DestroyCategory;
use App\Http\Requests\Admin\Category\IndexCategory;
use App\Http\Requests\Admin\Category\StoreCategory;
use App\Http\Requests\Admin\Category\UpdateCategory;
use App\Models\Category;
use App\Models\Post;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
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
        $data = $this->listingBuilder->for(Category::class)
            ->build()
            ->processRequestAndGet(
                $this->listingQueryBuilder->fromRequest(
                    $request,
                    [
                        'id',
                        'user_id',
                        'title',
                        'published_at',
                        'date_start',
                        'time_start',
                        'date_time_end',
                        'text',
                        'description',
                        'enabled',
                        'send',
                        'price',
                        'views',
                        'created_by_admin_user_id',
                        'updated_by_admin_user_id',
                        'created_at',
                        'updated_at',
                    ],
                    [
                        'id',
                        'title',
                        'slug',
                        'perex',
                        'text',
                        'description',
                    ],
                ),
                static function (Builder $query): void {
                    $query->with(['createdByAdminUser', 'updatedByAdminUser']);
                },
            );

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
                'createUrl' => $this->urlGenerator->route('admin/categories/create'),
                'editUrlTemplate' => $this->urlGenerator->route('admin/categories/edit', ['category' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/categories/update', ['category' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/categories/destroy', ['category' => ':id']),
            ],
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     */
    public function create(): View
    {
        $this->gate->authorize('admin.category.create');

        return $this->viewFactory->make(
            'admin.category.create',
            [
                'action' => $this->urlGenerator->route('admin/categories/store'),
                'posts' => Post::all(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategory $request): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $category = Category::create($data);
        $category->posts()->sync($request->getPostIds());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/categories/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/categories/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(Category $category): View
    {
        $this->gate->authorize('admin.category.edit', $category);

        $category->load(['createdByAdminUser', 'updatedByAdminUser']);

        $category->load('posts');

        return $this->viewFactory->make(
            'admin.category.edit',
            [
                'category' => $category,
                'action' => $this->urlGenerator->route('admin/categories/update', [$category]),
                'posts' => Post::all(),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategory $request, Category $category): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $category->update($data);
        if ($request->getPostIds() !== null) {
            $category->posts()->sync($request->getPostIds());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/categories/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
                'object' => $category,
            ];
        }

        return $this->redirector->route('admin/categories/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(DestroyCategory $request, Category $category): array|RedirectResponse
    {
        $category->delete();

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }
}
