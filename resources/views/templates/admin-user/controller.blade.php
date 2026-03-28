@php use Illuminate\Support\Arr;use Illuminate\Support\Str;echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};
@php
    $activation = $columns->search(function ($column, $key) {
            return $column['name'] === 'activated';
        }) !== false;
    $uses = [
        'App\Http\Controllers\Controller',
        'Brackets\AdminListing\Builders\ListingBuilder',
        'Brackets\AdminListing\Builders\ListingQueryBuilder',
        'Exception',
        'Illuminate\Auth\Access\AuthorizationException',
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Contracts\Auth\Factory as AuthFactory',
        'Illuminate\Contracts\Config\Repository as Config',
        'Illuminate\Contracts\Routing\UrlGenerator',
        'Illuminate\Contracts\View\Factory as ViewFactory',
        'Illuminate\Contracts\View\View',
        'Illuminate\Http\RedirectResponse',
        'Illuminate\Http\Request',
        'Illuminate\Routing\Redirector',
        'Symfony\Component\HttpKernel\Exception\HttpException',
        sprintf('App\Http\Requests\Admin\%s\Destroy%s', $modelWithNamespaceFromDefault, $modelBaseName),
        sprintf('App\Http\Requests\Admin\%s\ImpersonalLogin%s', $modelWithNamespaceFromDefault, $modelBaseName),
        sprintf('App\Http\Requests\Admin\%s\Index%s', $modelWithNamespaceFromDefault, $modelBaseName),
        sprintf('App\Http\Requests\Admin\%s\Store%s', $modelWithNamespaceFromDefault, $modelBaseName),
        sprintf('App\Http\Requests\Admin\%s\Update%s', $modelWithNamespaceFromDefault, $modelBaseName),
        $modelFullName,
    ];
    if ($export) {
        $uses[] = sprintf('App\Exports\%s', $exportBaseName);
        $uses[] = 'Maatwebsite\Excel\Excel';
        $uses[] = 'Symfony\Component\HttpFoundation\BinaryFileResponse';
    }
    if ($activation) {
        $uses[] = 'Brackets\AdminAuth\Activation\Contracts\ActivationBroker';
        $uses[] = 'Brackets\AdminAuth\Services\ActivationService';
    }

    $belongsToManyRelations = [];
    if (count($relations) > 0 && count($relations['belongsToMany']) > 0) {
        $belongsToManyRelations = $relations['belongsToMany'];
        foreach ($belongsToManyRelations as $belongsToMany) {
            $uses[] = $belongsToMany['related_model'];
        }
    }
    if (!$withoutBulk) {
        $uses[] = sprintf('App\Http\Requests\Admin\%s\BulkDestroy%s', $modelWithNamespaceFromDefault, $modelBaseName);
        $uses[] = 'Illuminate\Database\DatabaseManager';
    }
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

final class {{ $controllerBaseName }} extends Controller
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
    public function index(Index{{ $modelBaseName }} $request): array|View
    {
        $data = $this->listingBuilder->for({{ $modelBaseName }}::class)
            ->build()
            ->processRequestAndGet(
                $this->listingQueryBuilder->fromRequest(
                    $request,
                    [
@foreach($columnsToQuery as $column)
                        '{{ $column }}',
@endforeach
                    ],
                    [
@foreach($columnsToSearchIn as $column)
                        '{{ $column }}',
@endforeach
                    ],
                ),
            );

        if ($request->ajax()) {
@if(!$withoutBulk)
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id'),
                ];
            }

@endif
            return [
                'data' => $data,
            ];
        }

        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/{{ $resource }}/index'),
                'createUrl' => $this->urlGenerator->route('admin/{{ $resource }}/create'),
                'editUrlTemplate' => $this->urlGenerator->route('admin/{{ $resource }}/edit', ['{{ $modelVariableName }}' => ':id']),
                'updateUrlTemplate' => $this->urlGenerator->route('admin/{{ $resource }}/update', ['{{ $modelVariableName }}' => ':id']),
                'destroyUrlTemplate' => $this->urlGenerator->route('admin/{{ $resource }}/destroy', ['{{ $modelVariableName }}' => ':id']),
@if(!$withoutBulk)
                'bulkAllUrl' => $this->urlGenerator->route('admin/{{ $resource }}/index'),
                'bulkDestroyUrl' => $this->urlGenerator->route('admin/{{ $resource }}/bulk-destroy'),
@endif
@if($activation)
                'resendActivationUrlTemplate' => $this->urlGenerator->route(
                    'admin/{{ $resource }}/resend-activation-email',
                    ['{{ $modelVariableName }}' => ':id'],
                ),
@endif
                'impersonalLoginUrlTemplate' => $this->urlGenerator->route(
                    'admin/{{ $resource }}/impersonal-login',
                    ['{{ $modelVariableName }}' => ':id'],
                ),
@if($export)
                'exportUrl' => $this->urlGenerator->route('admin/{{ $resource }}/export'),
@endif
                'activation' => $this->config->get('admin-auth.activation_enabled'),
                'canImpersonalLogin' => $this->gate->check('admin.{{ $modelDotNotation }}.impersonal-login'),
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
        $this->gate->authorize('admin.{{ $modelDotNotation }}.create');

        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.create',
            [
                'action' => $this->urlGenerator->route('admin/{{ $resource }}/store'),
                'activation' => $this->config->get('admin-auth.activation_enabled'),
@foreach($belongsToManyRelations as $belongsToMany)
@if($belongsToMany['related_table'] === 'roles')
                '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::where('guard_name', $this->guard)->get(),
@else
                '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::all(),
@endif
@endforeach
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store{{ $modelBaseName }} $request): array|RedirectResponse
    {
        $data = $request->getModifiedData();

@if(count($belongsToManyRelations) > 0)
        ${{ $modelVariableName }} = {{ $modelBaseName }}::create($data);
@foreach($belongsToManyRelations as $belongsToMany)
        ${{ $modelVariableName }}->{{ $belongsToMany['related_table'] }}()->sync($request->get{{ $belongsToMany['related_model_name'] }}Ids());
@endforeach
@else
        {{ $modelBaseName }}::create($data);
@endif

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/{{ $resource }}/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/{{ $resource }}/index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit({{ $modelBaseName }} ${{ $modelVariableName }}): View
    {
        $this->gate->authorize('admin.{{ $modelDotNotation }}.edit', ${{ $modelVariableName }});

@if(count($belongsToManyRelations) > 0)
@foreach($belongsToManyRelations as $belongsToMany)
        ${{ $modelVariableName }}->load('{{ $belongsToMany['related_table'] }}');
@endforeach

@endif
        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.edit',
            [
                '{{ $modelVariableName }}' => ${{ $modelVariableName }},
                'action' => $this->urlGenerator->route('admin/{{ $resource }}/update', [${{ $modelVariableName }}]),
                'activation' => $this->config->get('admin-auth.activation_enabled'),
@foreach($belongsToManyRelations as $belongsToMany)
@if($belongsToMany['related_table'] === 'roles')
                '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::where('guard_name', $this->guard)->get(),
@else
                '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::all(),
@endif
@endforeach
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): array|RedirectResponse
    {
        $data = $request->getModifiedData();

        ${{ $modelVariableName }}->update($data);
@if(count($belongsToManyRelations) > 0)
@foreach($belongsToManyRelations as $belongsToMany)
        if ($request->get{{ $belongsToMany['related_model_name'] }}Ids() !== null) {
            ${{ $modelVariableName }}->{{ $belongsToMany['related_table'] }}()->sync($request->get{{ $belongsToMany['related_model_name'] }}Ids());
        }
@endforeach
@endif

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->route('admin/{{ $resource }}/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->route('admin/{{ $resource }}/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Exception
     */
    public function destroy(Destroy{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): array|RedirectResponse
    {
        ${{ $modelVariableName }}->delete();

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }
@if(!$withoutBulk)

    /**
     * Remove the specified resources from storage.
     *
     * @throws Exception
     */
    public function bulkDestroy(BulkDestroy{{ $modelBaseName }} $request, DatabaseManager $databaseManager): array|RedirectResponse
    {
        $databaseManager->transaction(static function () use ($request): void {
            $request->getIds()
                ->chunk(1000)
                ->each(static function ($bulkChunk): void {
                    {{ $modelBaseName }}::whereIn('id', $bulkChunk)
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
@endif
@if($export)

    /**
     * Export entities
     */
    public function export(Excel $excel, {{ $exportBaseName }} $export): ?BinaryFileResponse
    {
        return $excel->download($export, '{{ Str::plural($modelVariableName) }}.xlsx');
    }
@endif
@if($activation)

    /**
     * Resend activation e-mail
     *
     * @throws HttpException
     */
    public function resendActivationEmail(
        Request $request,
        ActivationService $activationService,
        {{ $modelBaseName }} ${{ $modelVariableName }},
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

        $response = $activationService->handle(${{ $modelVariableName }});
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
@endif

    /**
     * Impersonal login as admin user
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function impersonalLogin(
        ImpersonalLogin{{ $modelBaseName }} $request,
        {{ $modelBaseName }} ${{ $modelVariableName }},
        AuthFactory $auth,
    ): RedirectResponse {
        $auth->guard($this->guard)
            ->login(${{ $modelVariableName }});

        return $this->redirector->back();
    }
}
