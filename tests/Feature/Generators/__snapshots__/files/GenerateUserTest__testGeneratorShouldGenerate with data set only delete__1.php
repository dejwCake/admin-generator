<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\DestroyUser;
use App\Http\Requests\Admin\User\ImpersonalLoginUser;
use App\Http\Requests\Admin\User\IndexUser;
use App\Models\User;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/users/destroy', ['user' => ':id']),
                'resendVerifyEmailUrlTemplate' => $this->urlGenerator->route(
                    'admin/users/resend-verify-email',
                    ['user' => ':id'],
                ),
                'impersonalLoginUrlTemplate' => $this->urlGenerator->route(
                    'admin/users/impersonal-login',
                    ['user' => ':id'],
                ),
                'canImpersonalLogin' => $this->gate->check('admin.user.impersonal-login'),
            ],
        );
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
    public function impersonalLogin(ImpersonalLoginUser $request, User $user, AuthFactory $auth): RedirectResponse
    {
        $auth->guard($this->guard)
            ->login($user);

        return $this->redirector->back();
    }
}
