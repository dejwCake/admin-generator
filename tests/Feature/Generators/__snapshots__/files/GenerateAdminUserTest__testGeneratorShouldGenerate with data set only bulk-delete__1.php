<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUser\BulkDestroyAdminUser;
use App\Http\Requests\Admin\AdminUser\ImpersonalLoginAdminUser;
use App\Http\Requests\Admin\AdminUser\IndexAdminUser;
use Brackets\AdminAuth\Activation\Contracts\ActivationBroker;
use Brackets\AdminAuth\Models\AdminUser;
use Brackets\AdminAuth\Services\ActivationService;
use Brackets\AdminListing\Builders\ListingBuilder;
use Brackets\AdminListing\Builders\ListingQueryBuilder;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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
            'admin.admin-user.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/admin-users/index'),
                'bulkAllUrl' => $this->urlGenerator->route('admin/admin-users/index'),
                'bulkDestroyUrl' => $this->urlGenerator->route('admin/admin-users/bulk-destroy'),
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
     * Remove the specified resources from storage.
     *
     * @throws Exception
     */
    public function bulkDestroy(BulkDestroyAdminUser $request, DatabaseManager $databaseManager): array|RedirectResponse
    {
        $databaseManager->transaction(static function () use ($request): void {
            $request->getIds()
                ->chunk(1000)
                ->each(static function ($bulkChunk): void {
                    AdminUser::whereIn('id', $bulkChunk)
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
