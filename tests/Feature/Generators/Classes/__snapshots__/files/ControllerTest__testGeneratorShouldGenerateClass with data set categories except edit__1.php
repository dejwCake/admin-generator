<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\CategoriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\BulkDestroyCategory;
use App\Http\Requests\Admin\Category\DestroyCategory;
use App\Http\Requests\Admin\Category\ExportCategory;
use App\Http\Requests\Admin\Category\IndexCategory;
use App\Http\Requests\Admin\Category\StoreCategory;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Carbon\CarbonImmutable;
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
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        if ($trashed && !$request->has('bulk')) {
            $data->each(
                static fn (Category $category): Category => $category
                    ->setAttribute('restorable', $category->isRestorable()),
            );
        }

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
            'admin.category.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/categories/index'),
                'createUrl' => $this->urlGenerator->route('admin/categories/create'),
                'showUrlTemplate' => $this->urlGenerator->route('admin/categories/show', ['category' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/categories/destroy', ['category' => ':id']),
                'forceDestroyUrlTemplate' => $this->urlGenerator->route(
                    'admin/categories/force-destroy',
                    ['category' => ':id'],
                ),
                'restoreUrlTemplate' => $this->urlGenerator->route('admin/categories/restore', ['category' => ':id']),
                'bulkAllUrl' => $this->urlGenerator->route('admin/categories/index'),
                'bulkDestroyUrl' => $this->urlGenerator->route('admin/categories/bulk-destroy'),
                'bulkForceDestroyUrl' => $this->urlGenerator->route('admin/categories/bulk-force-destroy'),
                'bulkRestoreUrl' => $this->urlGenerator->route('admin/categories/bulk-restore'),
                'exportUrl' => $this->urlGenerator->route('admin/categories/export'),
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
                'users' => User::all(),
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
     * Display the specified resource.
     *
     * @throws AuthorizationException
     */
    public function show(Category $category): View
    {
        $this->gate->authorize('admin.category.show', $category);

        $category->load([
            'createdByAdminUser',
            'updatedByAdminUser',
            'user',
            'posts',
        ]);

        return $this->viewFactory->make(
            'admin.category.show',
            [
                'category' => $category,
                'backUrl' => $this->urlGenerator->route('admin/categories/index'),
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
     * Remove the specified resources from storage.
     *
     * @throws Exception
     */
    public function bulkDestroy(BulkDestroyCategory $request, DatabaseManager $databaseManager): array|RedirectResponse
    {
        $databaseManager->transaction(static function () use ($request): void {
            $request->getIds()
                ->chunk(1000)
                ->each(static function (Collection $bulkChunk): void {
                    Category::whereIn('id', $bulkChunk)
                        ->get()
                        ->each(static fn (Category $category): ?bool => $category->delete());
                });
        });

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }

    /**
     * Export entities
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function export(ExportCategory $request, Excel $excel, CategoriesExport $export): BinaryFileResponse
    {
        $currentTime = CarbonImmutable::now()->toDateTimeString();
        $nameOfExportedFile = sprintf('categories_%s.xlsx', $currentTime);

        return $excel->download($export, $nameOfExportedFile);
    }

    /**
     * @param Builder<Category> $query
     */
    private function onlyTrashed(Builder $query): void
    {
        $query->onlyTrashed();
    }
}
