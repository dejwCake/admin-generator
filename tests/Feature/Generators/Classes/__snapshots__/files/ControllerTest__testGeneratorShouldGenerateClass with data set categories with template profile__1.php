<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

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

final class CategoriesController extends Controller
{
    private Category $category;
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
        $this->category = $this->getUser($request);

        return $this->viewFactory->make(
            'admin.profile.edit-profile',
            [
                'category' => $this->category,
                'action' => $this->urlGenerator->route('admin/update-profile'),
                'avatarCollection' => $this->category->getCustomMediaCollection('avatar'),
                'avatarMedia' => $this->category->getThumbs200ForCollection('avatar'),
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
        $this->category = $this->getUser($request);

        $data = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
            ],
            'title' => [
                'sometimes',
                Rule::unique('categories', 'title')
                    ->ignore($this->category->getKey(), $this->category->getKeyName()),
                'string',
            ],
            'name' => [
                'nullable',
                'string',
            ],
            'first_name' => [
                'nullable',
                'string',
            ],
            'last_name' => [
                'nullable',
                'string',
            ],
            'subject' => [
                'nullable',
                'string',
            ],
            'email' => [
                'nullable',
                'email',
                'string',
            ],
            'language' => [
                'sometimes',
                'string',
            ],
            'slug' => [
                'sometimes',
                Rule::unique('categories', 'slug')
                    ->ignore($this->category->getKey(), $this->category->getKeyName()),
                'string',
            ],
            'perex' => [
                'nullable',
                'string',
            ],
            'long_text' => [
                'nullable',
                'string',
            ],
            'published_at' => [
                'nullable',
                'date',
            ],
            'date_start' => [
                'nullable',
                'date',
            ],
            'time_start' => [
                'nullable',
                Rule::date()->format('H:i:s'),
            ],
            'date_time_end' => [
                'nullable',
                'date',
            ],
            'released_at' => [
                'sometimes',
                'date',
            ],
            'text' => [
                'sometimes',
                'string',
            ],
            'description' => [
                'sometimes',
                'string',
            ],
            'enabled' => [
                'sometimes',
                'boolean',
            ],
            'send' => [
                'sometimes',
                'boolean',
            ],
            'price' => [
                'nullable',
                'numeric',
            ],
            'rating' => [
                'nullable',
                'numeric',
            ],
            'views' => [
                'sometimes',
                'integer',
            ],
            'created_by_admin_user_id' => [
                'nullable',
                'integer',
            ],
            'updated_by_admin_user_id' => [
                'nullable',
                'integer',
            ],
        ]);

        $this->category->update($data);

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
        $this->category = $this->getUser($request);

        return $this->viewFactory->make(
            'admin.profile.edit-password',
            [
                'category' => $this->category,
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
        $this->category = $this->getUser($request);

        $data = $request->validate([
            'password' => [
                'nullable',
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

        $this->category->update($data);

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
