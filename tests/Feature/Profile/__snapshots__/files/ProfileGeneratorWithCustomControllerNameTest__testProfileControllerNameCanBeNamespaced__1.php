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
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProfileController extends Controller
{
    /**
     * Guard used for admin user
     */
    private string $guard;

    private AdminUser $adminUser;

    public function __construct(
        public readonly Config $config,
        public readonly Hasher $hasher,
        public readonly Redirector $redirector,
        public readonly UrlGenerator $urlGenerator,
        public readonly ViewFactory $viewFactory,
    ) {
        // TODO add authorization
        $this->guard = $this->config->get('admin-auth.defaults.guard', 'admin');
    }

    /**
     * Show the form for editing logged user profile.
     */
    public function editProfile(Request $request): View
    {
        $this->setUser($request);

        return $this->viewFactory->make(
            'admin.profile.edit-profile',
            [
                'adminUser' => $this->adminUser,
                'action' => $this->urlGenerator->route('admin/update-profile'),
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
        $this->setUser($request);

        // Validate the request
        $request->validate([
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['sometimes', 'email', Rule::unique('admin_users', 'email')
                ->ignore($this->adminUser->getKey(), $this->adminUser->getKeyName())
                ->whereNull('deleted_at'), 'string'],
            'language' => ['sometimes', 'string'],
        ]);

        // Sanitize input
        $sanitized = $request->only([
            'first_name',
            'last_name',
            'email',
            'language',
        ]);

        // Update changed values AdminUser
        $this->adminUser->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/profile'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/profile');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editPassword(Request $request): View
    {
        $this->setUser($request);

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
        $this->setUser($request);

        // Validate the request
        $request->validate([
            'password' => ['sometimes', 'confirmed', 'min:7', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/', 'string'],
        ]);

        // Sanitize input
        $sanitized = $request->only([
            'password',
        ]);

        //Modify input, set hashed password
        $sanitized['password'] = $this->hasher->make($sanitized['password']);

        // Update changed values AdminUser
        $this->adminUser->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/password'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/password');
    }

    /**
     * Get logged user before each method
     *
     * @throws NotFoundHttpException
     */
    private function setUser(Request $request): void
    {
        if ($request->user($this->guard) === null) {
            throw NotFoundHttpException::fromStatusCode(
                404,
                trans('Admin User not found'),
            );
        }

        $this->adminUser = $request->user($this->guard);
    }
}
