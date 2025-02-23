<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\DestroyCategory;
use App\Http\Requests\Admin\Category\IndexCategory;
use App\Http\Requests\Admin\Category\StoreCategory;
use App\Http\Requests\Admin\Category\UpdateCategory;
use App\Models\Category;
use Brackets\AdminListing\Services\AdminListingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;

class CategoriesController extends Controller
{
    public function __construct(
        public readonly Gate $gate,
        public readonly Redirector $redirector,
        public readonly UrlGenerator $urlGenerator,
        public readonly ViewFactory $viewFactory,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexCategory $request): array|View
    {
        // create and AdminListingService instance for a specific model and
        $data = AdminListingService::create(Category::class)
            ->processRequestAndGet(
                // pass the request with params
                $request,
                // set columns to query
                ['id', 'user_id', 'title', 'published_at', 'date_start', 'time_start', 'date_time_end', 'text', 'description', 'enabled', 'send', 'price', 'views', 'created_by_admin_user_id', 'updated_by_admin_user_id', 'created_at', 'updated_at'],
                // set columns to searchIn
                ['id', 'title', 'slug', 'perex', 'text', 'description'],
                static function (Builder $query): void {
                    $query->with(['createdByAdminUser', 'updatedByAdminUser']);
                },
            );

        if ($request->ajax()) {

            return ['data' => $data];
        }

        return $this->viewFactory->make(
            'admin.category.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/categories/index'),
                'createUrl' => $this->urlGenerator->route('admin/categories/create'),
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
                'action' => $this->urlGenerator->to('admin/categories'),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategory $request): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getSanitized();
        $sanitized['created_by_admin_user_id'] = $request->user()->id;
        $sanitized['updated_by_admin_user_id'] = $request->user()->id;

        // Store the Category
        Category::create($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/categories'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/categories');
    }

    /**
     * Display the specified resource.
     *
     * @throws AuthorizationException
     */
    public function show(Category $category): void
    {
        $this->gate->authorize('admin.category.show', $category);

        // TODO your code goes here
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

        return $this->viewFactory->make(
            'admin.category.edit',
            [
                'category' => $category,
                'action' => $this->urlGenerator->route('admin/categories/update', [$category]),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategory $request, Category $category): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getSanitized();
        $sanitized['updated_by_admin_user_id'] = $request->user()->id;

        // Update changed values Category
        $category->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/categories'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
                'object' => $category,
            ];
        }

        return $this->redirector->to('admin/categories');
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
            return ['message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return $this->redirector->back();
    }

}
