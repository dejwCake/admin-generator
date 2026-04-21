<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Feed\Article\BulkDestroyArticle;
use App\Http\Requests\Admin\Feed\Article\DestroyArticle;
use App\Http\Requests\Admin\Feed\Article\IndexArticle;
use App\Http\Requests\Admin\Feed\Article\StoreArticle;
use App\Http\Requests\Admin\Feed\Article\UpdateArticle;
use App\Models\Category;
use App\Models\Feed\Article;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final class PostsController extends Controller
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
    public function index(IndexArticle $request): array|View
    {
        $data = $this->listingBuilder->for(Article::class)
            ->build()
            ->processRequestAndGet(
                $this->listingQueryBuilder->fromRequest(
                    $request,
                    [
                        'id',
                        'title',
                    ],
                    [
                        'id',
                        'title',
                    ],
                ),
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
            'admin.feed.article.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/feed-articles/index'),
                'createUrl' => $this->urlGenerator->route('admin/feed-articles/create'),
                'editUrlTemplate' => $this->urlGenerator->route('admin/feed-articles/edit', ['article' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/feed-articles/update', ['article' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/feed-articles/destroy', ['article' => ':id']),
                'bulkAllUrl' => $this->urlGenerator->route('admin/feed-articles/index'),
                'bulkDestroyUrl' => $this->urlGenerator->route('admin/feed-articles/bulk-destroy'),
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
        $this->gate->authorize('admin.feed.article.create');

        return $this->viewFactory->make(
            'admin.feed.article.create',
            [
                'action' => $this->urlGenerator->route('admin/feed-articles/store'),
                'categories' => Category::all(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticle $request): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $article = Article::create($data);
        $article->categories()->sync($request->getCategoryIds());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/feed-articles/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/feed-articles/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(Article $article): View
    {
        $this->gate->authorize('admin.feed.article.edit', $article);

        $article->load([
            'categories',
        ]);

        return $this->viewFactory->make(
            'admin.feed.article.edit',
            [
                'article' => $article,
                'action' => $this->urlGenerator->route('admin/feed-articles/update', [$article]),
                'categories' => Category::all(),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticle $request, Article $article): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $article->update($data);
        if ($request->getCategoryIds() !== null) {
            $article->categories()->sync($request->getCategoryIds());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/feed-articles/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/feed-articles/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(DestroyArticle $request, Article $article): array|RedirectResponse
    {
        $article->delete();

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
    public function bulkDestroy(BulkDestroyArticle $request, DatabaseManager $databaseManager): array|RedirectResponse
    {
        $databaseManager->transaction(static function () use ($request): void {
            $request->getIds()
                ->chunk(1000)
                ->each(static function ($bulkChunk): void {
                    Article::whereIn('id', $bulkChunk)
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
