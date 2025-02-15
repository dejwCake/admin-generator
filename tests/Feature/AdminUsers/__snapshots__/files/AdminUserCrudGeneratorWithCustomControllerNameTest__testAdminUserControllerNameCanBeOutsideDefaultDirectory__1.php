<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUser\DestroyAdminUser;
use App\Http\Requests\Admin\AdminUser\ImpersonalLoginAdminUser;
use App\Http\Requests\Admin\AdminUser\IndexAdminUser;
use App\Http\Requests\Admin\AdminUser\StoreAdminUser;
use App\Http\Requests\Admin\AdminUser\UpdateAdminUser;
use Brackets\AdminAuth\Activation\Contracts\ActivationBroker;
use Brackets\AdminAuth\Models\AdminUser;
use Brackets\AdminAuth\Services\ActivationService;
use Brackets\AdminListing\Services\AdminListingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\StatefulGuard;
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

class AdminUsersController extends Controller
{
    /**
     * Guard used for admin user
     */
    protected string $guard;

    public function __construct(
        public readonly Config $config,
        public readonly Gate $gate,
        public readonly Redirector $redirector,
        public readonly UrlGenerator $urlGenerator,
        public readonly ViewFactory $viewFactory,
    ) {
        $this->guard = $this->config->get('admin-auth.defaults.guard', 'admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexAdminUser $request): array|View
    {
        // create and AdminListingService instance for a specific model and
        $data = AdminListingService::create(AdminUser::class)
            ->processRequestAndGet(
                // pass the request with params
                $request,
                // set columns to query
                ['id', 'first_name', 'last_name', 'email', 'activated', 'forbidden', 'language'],
                // set columns to searchIn
                ['id'],
            );

        if ($request->ajax()) {
            return [
                'data' => $data,
                'activation' => $this->config->get('admin-auth.activation_enabled'),
            ];
        }

        return $this->viewFactory->make(
            'admin.admin-user.index',
            [
                'data' => $data,
                'activation' => $this->config->get('admin-auth.activation_enabled'),
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
        $this->gate->authorize('admin.admin-user.create');

        return $this->viewFactory->make(
            'admin.admin-user.create',
            [
                'action' => $this->urlGenerator->to('admin/admin-users'),
                'activation' => $this->config->get('admin-auth.activation_enabled'),
                'roles' => Role::where('guard_name', $this->guard)->get(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminUser $request): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getModifiedData();

        // Store the AdminUser
        $adminUser = AdminUser::create($sanitized);

        // But we do have a roles, so we need to attach the roles to the adminUser
        $adminUser->roles()->sync((new Collection($request->input('roles', [])))->map->id->toArray());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/admin-users'),
                'message' => __('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/admin-users');
    }

    /**
     * Display the specified resource.
     *
     * @throws AuthorizationException
     */
    public function show(AdminUser $adminUser): void
    {
        $this->gate->authorize('admin.admin-user.show', $adminUser);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(AdminUser $adminUser): View
    {
        $this->gate->authorize('admin.admin-user.edit', $adminUser);

        $adminUser->load('roles');

        return $this->viewFactory->make(
            'admin.admin-user.edit',
            [
                'adminUser' => $adminUser,
                'activation' => $this->config->get('admin-auth.activation_enabled'),
                'roles' => Role::where('guard_name', $this->guard)->get(),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminUser $request, AdminUser $adminUser): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getModifiedData();

        // Update changed values AdminUser
        $adminUser->update($sanitized);

        // But we do have a roles, so we need to attach the roles to the adminUser
        if ($request->input('roles')) {
            $adminUser->roles()->sync((new Collection($request->input('roles', [])))->map->id->toArray());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/admin-users'),
                'message' => __('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/admin-users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(DestroyAdminUser $request, AdminUser $adminUser): array|RedirectResponse
    {
        $adminUser->delete();

        if ($request->ajax()) {
            return ['message' => __('brackets/admin-ui::admin.operation.succeeded')];
        }

        return $this->redirector->back();
    }

    /**
     * Resend activation e-mail
     *
     * @throws HttpException
     */
    public function resendActivationEmail(
        Request $request,
        ActivationService $activationService,
        AdminUser $adminUser,
    ): array|RedirectResponse {
        if (!$this->config->get('admin-auth.activation_enabled')) {
            if ($request->ajax()) {
                throw HttpException::fromStatusCode(
                    400,
                    __('brackets/admin-ui::admin.operation.not_allowed'),
                );
            }

            return $this->redirector->back();
        }

        $response = $activationService->handle($adminUser);
        if ($response == ActivationBroker::ACTIVATION_LINK_SENT) {
            if ($request->ajax()) {
                return ['message' => __('brackets/admin-ui::admin.operation.succeeded')];
            }

            return $this->redirector->back();
        }

        if ($request->ajax()) {
            throw HttpException::fromStatusCode(
                409,
                __('brackets/admin-ui::admin.operation.failed'),
            );
        }

        return $this->redirector->back();
    }

    /**
     * Impersonal login as admin user
     */
    public function impersonalLogin(
        ImpersonalLoginAdminUser $request,
        AdminUser $adminUser,
        StatefulGuard $statefulGuard,
    ): RedirectResponse {
        $statefulGuard->login($adminUser);

        return $this->redirector->back();
    }
}
