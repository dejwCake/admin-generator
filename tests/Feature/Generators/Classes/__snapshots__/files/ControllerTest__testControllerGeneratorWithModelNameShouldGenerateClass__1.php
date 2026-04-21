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
use App\Models\Post;
use App\Models\User;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
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
    public function index(IndexCat $request): array|View
    {
        $data = $this->listingBuilder->for(Cat::class)
            ->build()
            ->processRequestAndGet(
                $this->listingQueryBuilder->fromRequest(
                    $request,
                    [
                        'id',
                        'user_id',
                        'title',
                        'name',
                        'first_name',
                        'last_name',
                        'subject',
                        'email',
                        'language',
                        'long_text',
                        'published_at',
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
                    ],
                    [
                        'id',
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
                static function (Builder $query): void {
                    $query->with([
                        'createdByAdminUser',
                        'updatedByAdminUser',
                        'user',
                    ]);
                },
            );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id'),
                ];
            }

            return [
                'data' => $data,
            ];
        }

        return $this->viewFactory->make(
            'admin.cat.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/cats/index'),
                'createUrl' => $this->urlGenerator->route('admin/cats/create'),
                'editUrlTemplate' => $this->urlGenerator->route('admin/cats/edit', ['cat' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/cats/update', ['cat' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/cats/destroy', ['cat' => ':id']),
                'bulkAllUrl' => $this->urlGenerator->route('admin/cats/index'),
                'bulkDestroyUrl' => $this->urlGenerator->route('admin/cats/bulk-destroy'),
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
                'action' => $this->urlGenerator->route('admin/cats/store'),
                'posts' => Post::all(),
                'users' => User::all(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCat $request): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $cat = Cat::create($data);
        $cat->posts()->sync($request->getPostIds());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/cats/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/cats/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(Cat $cat): View
    {
        $this->gate->authorize('admin.cat.edit', $cat);

        $cat->load([
            'createdByAdminUser',
            'updatedByAdminUser',
            'user',
            'posts',
        ]);

        return $this->viewFactory->make(
            'admin.cat.edit',
            [
                'cat' => $cat,
                'action' => $this->urlGenerator->route('admin/cats/update', [$cat]),
                'posts' => Post::all(),
                'users' => User::all(),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCat $request, Cat $cat): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $cat->update($data);
        if ($request->getPostIds() !== null) {
            $cat->posts()->sync($request->getPostIds());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/cats/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
                'object' => $cat,
            ];
        }

        return $this->redirector->route('admin/cats/index');
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
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
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
        $databaseManager->transaction(static function () use ($request): void {
            $request->getIds()
                ->chunk(1000)
                ->each(static function ($bulkChunk): void {
                    Cat::whereIn('id', $bulkChunk)
                        ->delete();
                });
        });

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }
}
