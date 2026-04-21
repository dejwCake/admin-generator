<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Post\DestroyPost;
use App\Http\Requests\Admin\Post\IndexPost;
use App\Http\Requests\Admin\Post\StorePost;
use App\Http\Requests\Admin\Post\UpdatePost;
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
    public function index(IndexPost $request): array|View
    {
        $data = $this->listingBuilder->for(Post::class)
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
            return [
                'data' => $data,
            ];
        }

        return $this->viewFactory->make(
            'admin.post.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/posts/index'),
                'createUrl' => $this->urlGenerator->route('admin/posts/create'),
                'editUrlTemplate' => $this->urlGenerator->route('admin/posts/edit', ['post' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/posts/update', ['post' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/posts/destroy', ['post' => ':id']),
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
        $this->gate->authorize('admin.post.create');

        return $this->viewFactory->make(
            'admin.post.create',
            [
                'action' => $this->urlGenerator->route('admin/posts/store'),
                'categories' => Category::all(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePost $request): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $post = Post::create($data);
        $post->categories()->sync($request->getCategoryIds());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/posts/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/posts/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(Post $post): View
    {
        $this->gate->authorize('admin.post.edit', $post);

        $post->load([
            'categories',
        ]);

        return $this->viewFactory->make(
            'admin.post.edit',
            [
                'post' => $post,
                'action' => $this->urlGenerator->route('admin/posts/update', [$post]),
                'categories' => Category::all(),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePost $request, Post $post): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $post->update($data);
        if ($request->getCategoryIds() !== null) {
            $post->categories()->sync($request->getCategoryIds());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/posts/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/posts/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(DestroyPost $request, Post $post): array|RedirectResponse
    {
        $post->delete();

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }
}
