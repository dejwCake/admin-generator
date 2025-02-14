@php use Illuminate\Support\Arr;echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};
@php
    $uses = [
        'App\Http\Controllers\Controller',
        'Illuminate\Contracts\Hashing\Hasher',
        'Illuminate\Contracts\Config\Repository as Config',
        'Illuminate\Contracts\View\Factory as ViewFactory',
        'Illuminate\Contracts\View\View',
        'Illuminate\Http\RedirectResponse',
        'Illuminate\Http\Request',
        'Illuminate\Routing\Redirector',
        'Illuminate\Validation\Rule',
        'Illuminate\Validation\ValidationException',
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
        $modelFullName,
    ];
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

class ProfileController extends Controller
{
    public {{ $modelBaseName }} ${{ $modelVariableName }};

    /**
     * Guard used for admin user
     */
    protected string $guard = 'admin';

    public function __construct(
        public readonly Config $config,
        public readonly Hasher $hasher,
        public readonly Redirector $redirector,
        public readonly UrlGenerator $urlGenerator,
        public readonly ViewFactory $viewFactory,
    ) {
        // TODO add authorization
        $this->guard = $this->config->get('admin-auth.defaults.guard');
    }

    /**
     * Get logged user before each method
     *
     * {{'@'}}throws NotFoundHttpException
     */
    protected function setUser(Request $request): void
    {
        if ($request->user($this->guard) === null) {
            throw NotFoundHttpException::fromStatusCode(
                404,
                __('Admin User not found'),
            );
        }

        $this->{{ $modelVariableName }} = $request->user($this->guard);
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
                '{{ $modelVariableName }}' => $this->{{ $modelVariableName }},
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
        $this->setUser($request);

        // Validate the request
        $request->validate([
            @foreach($columnsProfile as $column)'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
            @endforeach
        ]);

        // Sanitize input
        $sanitized = $request->only([
            @foreach($columnsProfile as $column)'{{ $column['name'] }}',
            @endforeach
        ]);

        // Update changed values {{ $modelBaseName }}
        $this->{{ $modelVariableName }}->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/profile'),
                'message' => __('brackets/admin-ui::admin.operation.succeeded'),
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
                '{{ $modelVariableName }}' => $this->{{ $modelVariableName }},
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
        $this->setUser($request);

        // Validate the request
        $request->validate([
            @foreach($columnsPassword as $column)'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
            @endforeach
        ]);

        // Sanitize input
        $sanitized = $request->only([
            @foreach($columnsPassword as $column)'{{ $column['name'] }}',
            @endforeach
        ]);

        //Modify input, set hashed password
        $sanitized['password'] = $this-hasher->make($sanitized['password']);

        // Update changed values {{ $modelBaseName }}
        $this->{{ $modelVariableName }}->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/password'),
                'message' => __('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/password');
    }
}
