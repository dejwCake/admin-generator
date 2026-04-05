@php
    use Illuminate\Support\Arr;
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};
@php
    $uses = [
        'App\Http\Controllers\Controller',
        'Brackets\AdminAuth\Models\AdminUser',
        'Illuminate\Contracts\Config\Repository as Config',
        'Illuminate\Contracts\Hashing\Hasher',
        'Illuminate\Contracts\Routing\UrlGenerator',
        'Illuminate\Contracts\View\Factory as ViewFactory',
        'Illuminate\Contracts\View\View',
        'Illuminate\Http\RedirectResponse',
        'Illuminate\Http\Request',
        'Illuminate\Routing\Redirector',
        'Illuminate\Validation\Rule',
        'Illuminate\Validation\Rules\Password',
        'Illuminate\Validation\ValidationException',
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
    ];
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

final class {{ $controllerBaseName }} extends Controller
{
    private {{ $modelBaseName }} ${{ $modelVariableName }};
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
        $this->{{ $modelVariableName }} = $this->getUser($request);

        return $this->viewFactory->make(
            'admin.profile.edit-profile',
            [
                '{{ $modelVariableName }}' => $this->{{ $modelVariableName }},
                'action' => $this->urlGenerator->route('admin/update-profile'),
                'avatarCollection' => $this->{{ $modelVariableName }}->getCustomMediaCollection('avatar'),
                'avatarMedia' => $this->{{ $modelVariableName }}->getThumbs200ForCollection('avatar'),
            ],
        );
    }
@php
    $columnsProfile = $columns->reject(function($column) {
        return in_array($column['name'], ['password', 'activated', 'forbidden']);
    });
@endphp

    /**
     * Update the specified resource in storage.
     *
     * {{'@'}}throws ValidationException
     */
    public function updateProfile(Request $request): array|RedirectResponse
    {
        $this->{{ $modelVariableName }} = $this->getUser($request);

        $data = $request->validate([
@foreach($columnsProfile as $column)
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverUpdateRules']) !!},
            ],
@endforeach
        ]);

        $this->{{ $modelVariableName }}->update($data);

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
        $this->{{ $modelVariableName }} = $this->getUser($request);

        return $this->viewFactory->make(
            'admin.profile.edit-password',
            [
                '{{ $modelVariableName }}' => $this->{{ $modelVariableName }},
                'action' => $this->urlGenerator->route('admin/update-password'),
            ],
        );
    }

@php
    $columnsPassword = $columns->reject(function($column) {
        return !in_array($column['name'], ['password']);
    });
@endphp
    /**
     * Update the specified resource in storage.
     *
     * {{'@'}}throws ValidationException
     */
    public function updatePassword(Request $request): array|RedirectResponse
    {
        $this->{{ $modelVariableName }} = $this->getUser($request);

        $data = $request->validate([
@foreach($columnsPassword as $column)
            '{{ $column['name'] }}' => [
                {!! implode(",\n                ", (array) $column['serverUpdateRules']) !!},
            ],
@endforeach
        ]);

        $data['password'] = $this->hasher->make($data['password']);

        $this->{{ $modelVariableName }}->update($data);

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
     * {{'@'}}throws NotFoundHttpException
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
