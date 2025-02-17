<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\DestroyUser;
use App\Http\Requests\Admin\User\IndexUser;
use App\Http\Requests\Admin\User\StoreUser;
use App\Http\Requests\Admin\User\UpdateUser;
use App\Models\User;
use Brackets\AdminListing\Services\AdminListingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsersController extends Controller
{
    public function __construct(
        public readonly Config $config,
        public readonly Gate $gate,
        public readonly Redirector $redirector,
        public readonly UrlGenerator $urlGenerator,
        public readonly ViewFactory $viewFactory,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexUser $request): array|View
    {
        // create and AdminListingService instance for a specific model and
        $data = AdminListingService::create(User::class)
            ->processRequestAndGet(
                // pass the request with params
                $request,
                // set columns to query
                ['id', 'name', 'email', 'email_verified_at'],
                // set columns to searchIn
                ['id', 'name', 'email'],
            );

        if ($request->ajax()) {
            return [
                'data' => $data,
            ];
        }

        return $this->viewFactory->make(
            'admin.user.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/users/index'),
                'createUrl' => $this->urlGenerator->route('admin/users/create'),
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
        $this->gate->authorize('admin.user.create');

        return $this->viewFactory->make(
            'admin.user.create',
            [
                'action' => $this->urlGenerator->to('admin/users'),
                'roles' => Role::all(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUser $request): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getModifiedData();

        // Store the User
        $user = User::create($sanitized);

        // But we do have a roles, so we need to attach the roles to the user
        $user->roles()->sync((new Collection($request->input('roles', [])))->map->id->toArray());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/users'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/users');
    }

    /**
     * Display the specified resource.
     *
     * @throws AuthorizationException
     */
    public function show(User $user): void
    {
        $this->gate->authorize('admin.user.show', $user);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(User $user): View
    {
        $this->gate->authorize('admin.user.edit', $user);

        $user->load('roles');

        return $this->viewFactory->make(
            'admin.user.edit',
            [
                'user' => $user,
                'action' => $this->urlGenerator->route('admin/users/update', [$user]),
                'roles' => Role::all(),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUser $request, User $user): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getModifiedData();

        // Update changed values User
        $user->update($sanitized);

        // But we do have a roles, so we need to attach the roles to the user
        if ($request->input('roles')) {
            $user->roles()->sync((new Collection($request->input('roles', [])))->map->id->toArray());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/users'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/users');
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
            return ['message' => trans('brackets/admin-ui::admin.operation.succeeded')];
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
            return ['message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return $this->redirector->back();
    }
}
