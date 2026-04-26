<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\User\BulkDestroyUser;
use App\Http\Requests\Admin\Auth\User\DestroyUser;
use App\Http\Requests\Admin\Auth\User\ImpersonalLoginUser;
use App\Http\Requests\Admin\Auth\User\IndexUser;
use App\Http\Requests\Admin\Auth\User\StoreUser;
use App\Http\Requests\Admin\Auth\User\UpdateUser;
use App\Models\Auth\User;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class UsersController extends Controller
{
    private readonly string $guard;

    public function __construct(
        private readonly Config $config,
        private readonly Gate $gate,
        private readonly Redirector $redirector,
        private readonly UrlGenerator $urlGenerator,
        private readonly ViewFactory $viewFactory,
        private readonly ListingBuilder $listingBuilder,
        private readonly ListingQueryBuilder $listingQueryBuilder,
    ) {
        $this->guard = $this->config->get('auth.defaults.guard', 'web');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexUser $request): array|View
    {
        $data = $this->listingBuilder->for(User::class)
            ->build()
            ->processRequestAndGet(
                $this->listingQueryBuilder->fromRequest(
                    $request,
                    [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                    ],
                    [
                        'id',
                        'name',
                        'email',
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
            'admin.auth.user.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/auth-users/index'),
                'createUrl' => $this->urlGenerator->route('admin/auth-users/create'),
                'editUrlTemplate' => $this->urlGenerator->route('admin/auth-users/edit', ['user' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/auth-users/update', ['user' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/auth-users/destroy', ['user' => ':id']),
                'bulkAllUrl' => $this->urlGenerator->route('admin/auth-users/index'),
                'bulkDestroyUrl' => $this->urlGenerator->route('admin/auth-users/bulk-destroy'),
                'resendVerifyEmailUrlTemplate' => $this->urlGenerator->route(
                    'admin/auth-users/resend-verify-email',
                    ['user' => ':id'],
                ),
                'impersonalLoginUrlTemplate' => $this->urlGenerator->route(
                    'admin/auth-users/impersonal-login',
                    ['user' => ':id'],
                ),
                'canImpersonalLogin' => $this->gate->check('admin.auth.user.impersonal-login'),
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
        $this->gate->authorize('admin.auth.user.create');

        return $this->viewFactory->make(
            'admin.auth.user.create',
            [
                'action' => $this->urlGenerator->route('admin/auth-users/store'),
                'roles' => Role::where('guard_name', $this->guard)->get(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUser $request): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $user = User::create($data);
        $user->roles()->sync($request->getRoleIds());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/auth-users/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/auth-users/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(User $user): View
    {
        $this->gate->authorize('admin.auth.user.edit', $user);

        $user->load([
            'roles',
        ]);

        return $this->viewFactory->make(
            'admin.auth.user.edit',
            [
                'user' => $user,
                'action' => $this->urlGenerator->route('admin/auth-users/update', [$user]),
                'roles' => Role::where('guard_name', $this->guard)->get(),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUser $request, User $user): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        $user->update($data);
        if ($request->getRoleIds() !== null) {
            $user->roles()->sync($request->getRoleIds());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/auth-users/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/auth-users/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(DestroyUser $request, User $user): array|RedirectResponse
    {
        $user->delete();

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
    public function bulkDestroy(BulkDestroyUser $request, DatabaseManager $databaseManager): array|RedirectResponse
    {
        $databaseManager->transaction(static function () use ($request): void {
            $request->getIds()
                ->chunk(1000)
                ->each(static function ($bulkChunk): void {
                    User::whereIn('id', $bulkChunk)
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

    /**
     * Resend verify e-mail
     */
    public function resendVerifyEmail(Request $request, User $user): array|RedirectResponse
    {
        if (!($user instanceof MustVerifyEmail) || $user->hasVerifiedEmail()) {
            if ($request->ajax()) {
                throw HttpException::fromStatusCode(
                    400,
                    trans('brackets/admin-ui::admin.operation.not_allowed'),
                );
            }

            return $this->redirector->back();
        }

        $user->sendEmailVerificationNotification();
        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }

    /**
     * Impersonal login as user
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function impersonalLogin(
        ImpersonalLoginUser $request,
        User $user,
        AuthFactory $auth,
    ): RedirectResponse {
        $auth->guard($this->guard)
            ->login($user);

        return $this->redirector->back();
    }
}
