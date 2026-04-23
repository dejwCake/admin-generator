<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\AdminUsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUser\DestroyAdminUser;
use App\Http\Requests\Admin\AdminUser\ExportAdminUser;
use App\Http\Requests\Admin\AdminUser\ImpersonalLoginAdminUser;
use App\Http\Requests\Admin\AdminUser\IndexAdminUser;
use App\Http\Requests\Admin\AdminUser\StoreAdminUser;
use App\Http\Requests\Admin\AdminUser\UpdateAdminUser;
use Brackets\AdminAuth\Activation\Contracts\ActivationBroker;
use Brackets\AdminAuth\Models\AdminUser;
use Brackets\AdminAuth\Services\ActivationService;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Maatwebsite\Excel\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class AdminUsersController extends Controller
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
        $this->guard = $this->config->get('admin-auth.defaults.guard', 'admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexAdminUser $request): array|View
    {
        $data = $this->listingBuilder->for(AdminUser::class)
            ->build()
            ->processRequestAndGet(
                $this->listingQueryBuilder->fromRequest(
                    $request,
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'activated',
                        'forbidden',
                        'language',
                    ],
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'language',
                    ],
                ),
            );

        if ($request->ajax()) {
            return [
                'data' => $data,
            ];
        }

        return $this->viewFactory->make(
            'admin.admin-user.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/admin-users/index'),
                'createUrl' => $this->urlGenerator->route('admin/admin-users/create'),
                'editUrlTemplate' => $this->urlGenerator->route('admin/admin-users/edit', ['adminUser' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/admin-users/update', ['adminUser' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/admin-users/destroy', ['adminUser' => ':id']),
                'exportUrl' => $this->urlGenerator->route('admin/admin-users/export'),
                'resendActivationUrlTemplate' => $this->urlGenerator->route(
                    'admin/admin-users/resend-activation-email',
                    ['adminUser' => ':id'],
                ),
                'impersonalLoginUrlTemplate' => $this->urlGenerator->route(
                    'admin/admin-users/impersonal-login',
                    ['adminUser' => ':id'],
                ),
                'activation' => $this->config->get('admin-auth.activation_enabled'),
                'canImpersonalLogin' => $this->gate->check('admin.admin-user.impersonal-login'),
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
                'action' => $this->urlGenerator->route('admin/admin-users/store'),
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
        $data = $request->getModifiedData();

        $adminUser = AdminUser::create($data);
        $adminUser->roles()->sync($request->getRoleIds());

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/admin-users/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/admin-users/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(AdminUser $adminUser): View
    {
        $this->gate->authorize('admin.admin-user.edit', $adminUser);

        $adminUser->load([
            'roles',
        ]);

        return $this->viewFactory->make(
            'admin.admin-user.edit',
            [
                'adminUser' => $adminUser,
                'action' => $this->urlGenerator->route('admin/admin-users/update', [$adminUser]),
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
        $data = $request->getModifiedData();

        $adminUser->update($data);
        if ($request->getRoleIds() !== null) {
            $adminUser->roles()->sync($request->getRoleIds());
        }

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/admin-users/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/admin-users/index');
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
    public function export(ExportAdminUser $request, Excel $excel, AdminUsersExport $export): BinaryFileResponse
    {
        $currentTime = CarbonImmutable::now()->toDateTimeString();
        $nameOfExportedFile = sprintf('adminUsers_%s.xlsx', $currentTime);

        return $excel->download($export, $nameOfExportedFile);
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
                    trans('brackets/admin-ui::admin.operation.not_allowed'),
                );
            }

            return $this->redirector->back();
        }

        $response = $activationService->handle($adminUser);
        if ($response === ActivationBroker::ACTIVATION_LINK_SENT) {
            if ($request->ajax()) {
                return [
                    'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
                ];
            }

            return $this->redirector->back();
        }

        if ($request->ajax()) {
            throw HttpException::fromStatusCode(
                409,
                trans('brackets/admin-ui::admin.operation.failed'),
            );
        }

        return $this->redirector->back();
    }

    /**
     * Impersonal login as admin user
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function impersonalLogin(
        ImpersonalLoginAdminUser $request,
        AdminUser $adminUser,
        AuthFactory $auth,
    ): RedirectResponse {
        $auth->guard($this->guard)
            ->login($adminUser);

        return $this->redirector->back();
    }
}
