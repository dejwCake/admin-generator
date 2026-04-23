<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\DestroyUser;
use App\Http\Requests\Admin\User\ExportUser;
use App\Http\Requests\Admin\User\IndexUser;
use App\Http\Requests\Admin\User\StoreUser;
use App\Http\Requests\Admin\User\UpdateUser;
use App\Models\User;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Carbon\CarbonImmutable;
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
use Maatwebsite\Excel\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
                'editUrlTemplate' => $this->urlGenerator->route('admin/users/edit', ['user' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/users/update', ['user' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/users/destroy', ['user' => ':id']),
                'exportUrl' => $this->urlGenerator->route('admin/users/export'),
                'resendVerifyEmailUrlTemplate' => $this->urlGenerator->route(
                    'admin/users/resend-verify-email',
                    ['user' => ':id'],
                ),
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
                'action' => $this->urlGenerator->route('admin/users/store'),
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
                'redirect' => $this->urlGenerator->route('admin/users/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/users/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(User $user): View
    {
        $this->gate->authorize('admin.user.edit', $user);

        $user->load([
            'roles',
        ]);

        return $this->viewFactory->make(
            'admin.user.edit',
            [
                'user' => $user,
                'action' => $this->urlGenerator->route('admin/users/update', [$user]),
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
                'redirect' => $this->urlGenerator->route('admin/users/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/users/index');
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
     * Export entities
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function export(ExportUser $request, Excel $excel, UsersExport $export): BinaryFileResponse
    {
        $currentTime = CarbonImmutable::now()->toDateTimeString();
        $nameOfExportedFile = sprintf('users_%s.xlsx', $currentTime);

        return $excel->download($export, $nameOfExportedFile);
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
}
