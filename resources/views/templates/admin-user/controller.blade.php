@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    assert($relations instanceof RelationCollection);
    assert($queryColumns instanceof ColumnCollection);
    assert($searchInColumns instanceof ColumnCollection);
    assert($visibleColumns instanceof ColumnCollection);
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};
@php
    $hasActivation = $visibleColumns->hasByName('activated');
    $uses = new Collection([
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
    ]);
    if ($hasExport) {
        $uses->push(sprintf('App\Exports\%s', $exportBaseName));
        $uses->push('Maatwebsite\Excel\Excel');
        $uses->push('Symfony\Component\HttpFoundation\BinaryFileResponse');
        $uses->push('Carbon\CarbonImmutable');
        $uses->push(sprintf('App\Http\Requests\Admin\%s\Export%s', $modelWithNamespaceFromDefault, $modelBaseName));
    }
    if ($hasActivation) {
        $uses->push('Brackets\AdminAuth\Activation\Contracts\ActivationBroker');
        $uses->push('Brackets\AdminAuth\Services\ActivationService');
    }

    if ($relations->hasBelongsToMany()) {
        foreach ($relations->getBelongsToMany() as $belongsToMany) {
            $uses->push($belongsToMany->relatedModel);
        }
    }
    if ($hasBulk) {
        $uses->push(sprintf('App\Http\Requests\Admin\%s\BulkDestroy%s', $modelWithNamespaceFromDefault, $modelBaseName));
        $uses->push('Illuminate\Database\DatabaseManager');
    }
    $uses = $uses->unique()->sort();
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
@foreach($queryColumns as $column)
                        '{{ $column->name }}',
@endforeach
                    ],
                    [
@foreach($searchInColumns as $column)
                        '{{ $column->name }}',
@endforeach
                    ],
                ),
@php
    $eagerLoads = new Collection([]);
    foreach ($relations->getBelongsTo() as $belongsTo) {
        $eagerLoads->push($belongsTo->relationMethodName);
    }
@endphp
@if($eagerLoads->isNotEmpty())
                static function (Builder $query): void {
                    $query->with([
@foreach($eagerLoads as $eagerLoad)
                        '{{ $eagerLoad }}',
@endforeach
                    ]);
                },
@endif
            );

        if ($request->ajax()) {
@if($hasBulk)
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
@if($hasBulk)
                'bulkAllUrl' => $this->urlGenerator->route('admin/{{ $resource }}/index'),
                'bulkDestroyUrl' => $this->urlGenerator->route('admin/{{ $resource }}/bulk-destroy'),
@endif
@if($hasExport)
                'exportUrl' => $this->urlGenerator->route('admin/{{ $resource }}/export'),
@endif
@if($hasActivation)
                'resendActivationUrlTemplate' => $this->urlGenerator->route(
                    'admin/{{ $resource }}/resend-activation-email',
                    ['{{ $modelVariableName }}' => ':id'],
                ),
@endif
                'impersonalLoginUrlTemplate' => $this->urlGenerator->route(
                    'admin/{{ $resource }}/impersonal-login',
                    ['{{ $modelVariableName }}' => ':id'],
                ),
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

@if($mediaCollections->isNotEmpty())
        ${{ $modelVariableName }}Model = new {{ $modelBaseName }}();

@endif
        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.create',
            [
                'action' => $this->urlGenerator->route('admin/{{ $resource }}/store'),
                'activation' => $this->config->get('admin-auth.activation_enabled'),
@foreach($relations->getBelongsToMany() as $belongsToMany)
@if($belongsToMany->relatedTable === 'roles')
                '{{ $belongsToMany->relatedTable }}' => {{ $belongsToMany->relatedModelName }}::where('guard_name', $this->guard)->get(),
@else
                '{{ $belongsToMany->relatedTable }}' => {{ $belongsToMany->relatedModelName }}::all(),
@endif
@endforeach
@foreach($relations->getBelongsTo() as $belongsTo)
@if(!$relations->hasRelatedTableInBelongsToMany($belongsTo->relatedTable))
                '{{ $belongsTo->relatedTable }}' => {{ $belongsTo->relatedModelName }}::all(),
@endif
@endforeach
@foreach($mediaCollections as $collection)
                '{{ $collection->collectionName }}Collection' => ${{ $modelVariableName }}Model->getCustomMediaCollection('{{ $collection->collectionName }}'),
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

@if($relations->hasBelongsToMany())
        ${{ $modelVariableName }} = {{ $modelBaseName }}::create($data);
@foreach($relations->getBelongsToMany() as $belongsToMany)
        ${{ $modelVariableName }}->{{ $belongsToMany->relatedTable }}()->sync($request->get{{ $belongsToMany->relatedModelName }}Ids());
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

@php
    $eagerLoads = new Collection([]);
    foreach ($relations->getBelongsToMany() as $belongsToMany) {
        $eagerLoads->push($belongsToMany->relationMethodName);
    }
    foreach ($relations->getBelongsTo() as $belongsTo) {
        $eagerLoads->push($belongsTo->relationMethodName);
    }
@endphp
@if($eagerLoads->isNotEmpty())
        ${{ $modelVariableName }}->load([
@foreach($eagerLoads as $eagerLoad)
            '{{ $eagerLoad }}',
@endforeach
        ]);

@endif
        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.edit',
            [
                '{{ $modelVariableName }}' => ${{ $modelVariableName }},
                'action' => $this->urlGenerator->route('admin/{{ $resource }}/update', [${{ $modelVariableName }}]),
                'activation' => $this->config->get('admin-auth.activation_enabled'),
@foreach($relations->getBelongsToMany() as $belongsToMany)
@if($belongsToMany->relatedTable === 'roles')
                '{{ $belongsToMany->relatedTable }}' => {{ $belongsToMany->relatedModelName }}::where('guard_name', $this->guard)->get(),
@else
                '{{ $belongsToMany->relatedTable }}' => {{ $belongsToMany->relatedModelName }}::all(),
@endif
@endforeach
@foreach($relations->getBelongsTo() as $belongsTo)
@if(!$relations->hasRelatedTableInBelongsToMany($belongsTo->relatedTable))
                '{{ $belongsTo->relatedTable }}' => {{ $belongsTo->relatedModelName }}::all(),
@endif
@endforeach
@foreach($mediaCollections as $collection)
                '{{ $collection->collectionName }}Collection' => ${{ $modelVariableName }}->getCustomMediaCollection('{{ $collection->collectionName }}'),
@if($collection->isImage())
                '{{ $collection->collectionName }}Media' => ${{ $modelVariableName }}->getThumbs200ForCollection('{{ $collection->collectionName }}'),
@else
                '{{ $collection->collectionName }}Media' => ${{ $modelVariableName }}->getMedia('{{ $collection->collectionName }}'),
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
@if($relations->hasBelongsToMany())
@foreach($relations->getBelongsToMany() as $belongsToMany)
        if ($request->get{{ $belongsToMany->relatedModelName }}Ids() !== null) {
            ${{ $modelVariableName }}->{{ $belongsToMany->relatedTable }}()->sync($request->get{{ $belongsToMany->relatedModelName }}Ids());
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
@if($hasBulk)

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
@if($hasExport)

    /**
     * Export entities
     *
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function export(Export{{ $modelBaseName }} $request, Excel $excel, {{ $exportBaseName }} $export): BinaryFileResponse
    {
        $currentTime = CarbonImmutable::now()->toDateTimeString();
        $nameOfExportedFile = sprintf('{{ Str::plural($modelVariableName) }}_%s.xlsx', $currentTime);

        return $excel->download($export, $nameOfExportedFile);
    }
@endif
@if($hasActivation)

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
