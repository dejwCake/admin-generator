<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Billing\Cat;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cat\BulkDestroyCat;
use App\Http\Requests\Admin\Cat\DestroyCat;
use App\Http\Requests\Admin\Cat\IndexCat;
use App\Http\Requests\Admin\Cat\StoreCat;
use App\Http\Requests\Admin\Cat\UpdateCat;
use Brackets\AdminListing\Services\AdminListingService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
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
    public function index(IndexCat $request): array|View
    {
        // create and AdminListingService instance for a specific model and
        $data = AdminListingService::create(Cat::class)
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
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id'),
                ];
            }

            return ['data' => $data];
        }

        return $this->viewFactory->make(
            'admin.cat.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/cats/index'),
                'createUrl' => $this->urlGenerator->route('admin/cats/create'),
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
        $this->gate->authorize('admin.cat.create');

        return $this->viewFactory->make(
            'admin.cat.create',
            [
                'action' => $this->urlGenerator->to('admin/cats'),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCat $request, Config $config): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getSanitized();
        $adminUserGuard = $config->get('admin-auth.defaults.guard', 'admin');
        $sanitized['created_by_admin_user_id'] = $request->user($adminUserGuard)->id;
        $sanitized['updated_by_admin_user_id'] = $request->user($adminUserGuard)->id;

        // Store the Cat
        Cat::create($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/cats'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/cats');
    }

    /**
     * Display the specified resource.
     *
     * @throws AuthorizationException
     */
    public function show(Cat $cat): void
    {
        $this->gate->authorize('admin.cat.show', $cat);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(Cat $cat): View
    {
        $this->gate->authorize('admin.cat.edit', $cat);

        $cat->load(['createdByAdminUser', 'updatedByAdminUser']);

        return $this->viewFactory->make(
            'admin.cat.edit',
            [
                'cat' => $cat,
                'action' => $this->urlGenerator->route('admin/cats/update', [$cat]),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCat $request, Cat $cat, Config $config): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getSanitized();
        $adminUserGuard = $config->get('admin-auth.defaults.guard', 'admin');
        $sanitized['updated_by_admin_user_id'] = $request->user($adminUserGuard)->id;

        // Update changed values Cat
        $cat->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/cats'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
                'object' => $cat,
            ];
        }

        return $this->redirector->to('admin/cats');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(DestroyCat $request, Cat $cat): array|RedirectResponse
    {
        $cat->delete();

        if ($request->ajax()) {
            return ['message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return $this->redirector->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @throws Exception
     */
    public function bulkDestroy(BulkDestroyCat $request, DatabaseManager $databaseManager): array|RedirectResponse
    {
        $databaseManager->transaction(static function () use ($request, $databaseManager): void {
            (new Collection($request->data['ids']))
                ->chunk(1000)
                ->each(static function ($bulkChunk) use ($databaseManager): void {
                    $databaseManager->table('cats')
                        ->whereIn('id', $bulkChunk)
                        ->update([
                            'deleted_at' => CarbonImmutable::now(),
                        ]);

                    // TODO your code goes here
                });
        });

        if ($request->ajax()) {
            return ['message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return $this->redirector->back();
    }
}
