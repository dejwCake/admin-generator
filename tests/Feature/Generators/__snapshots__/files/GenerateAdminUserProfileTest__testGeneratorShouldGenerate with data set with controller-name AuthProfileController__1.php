<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Brackets\AdminAuth\Models\AdminUser;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProfileController extends Controller
{
    private AdminUser $adminUser;
    private string $guard;

    public function __construct(
        private readonly Config $config,
        private readonly Hasher $hasher,
        private readonly Redirector $redirector,
        private readonly UrlGenerator $urlGenerator,
        private readonly ViewFactory $viewFactory,
    ) {
        $this->guard = $this->config->get('admin-auth.defaults.guard', 'admin');
    }

    /**
     * Show the form for editing a logged user profile.
     */
    public function editProfile(Request $request): View
    {
        $this->adminUser = $this->getUser($request);

        return $this->viewFactory->make(
            'admin.profile.edit-profile',
            [
                'adminUser' => $this->adminUser,
                'action' => $this->urlGenerator->route('admin/update-profile'),
                'avatarCollection' => $this->adminUser->getCustomMediaCollection('avatar'),
                'avatarMedia' => $this->adminUser->getThumbs200ForCollection('avatar'),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws ValidationException
     */
    public function updateProfile(Request $request): array|RedirectResponse
    {
        $this->adminUser = $this->getUser($request);

        $data = $request->validate([
            'first_name' => [
                'nullable',
                'string',
            ],
            'last_name' => [
                'nullable',
                'string',
            ],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('admin_users', 'email')
                    ->ignore($this->adminUser->getKey(), $this->adminUser->getKeyName())
                    ->whereNull('deleted_at'),
                'string',
            ],
            'language' => [
                'sometimes',
                'string',
            ],
        ]);

        $this->adminUser->update($data);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/edit-profile'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/edit-profile');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editPassword(Request $request): View
    {
        $this->adminUser = $this->getUser($request);

        return $this->viewFactory->make(
            'admin.profile.edit-password',
            [
                'adminUser' => $this->adminUser,
                'action' => $this->urlGenerator->route('admin/update-password'),
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws ValidationException
     */
    public function updatePassword(Request $request): array|RedirectResponse
    {
        $this->adminUser = $this->getUser($request);

        $data = $request->validate([
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'string',
            ],
        ]);

        $data['password'] = $this->hasher->make($data['password']);

        $this->adminUser->update($data);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/edit-password'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/edit-password');
    }

    /**
     * Get a logged user before each method
     *
     * @throws NotFoundHttpException
     */
    private function getUser(Request $request): AdminUser
    {
        if ($request->user($this->guard) === null) {
            throw NotFoundHttpException::fromStatusCode(
                404,
                trans('Admin User not found'),
            );
        }

        return $request->user($this->guard);
    }
}
