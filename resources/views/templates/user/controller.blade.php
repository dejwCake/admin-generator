@php use Illuminate\Support\Arr;echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};
@php
    $activation = $columns->search(function ($column, $key) {
            return $column['name'] === 'activated';
        }) !== false;
    $uses = [
        'App\Http\Controllers\Controller',
        'Brackets\AdminListing\Services\AdminListingService',
        'Exception',
        'Illuminate\Auth\Access\AuthorizationException',
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Contracts\Config\Repository as Config',
        'Illuminate\Contracts\Routing\UrlGenerator',
        'Illuminate\Contracts\View\Factory as ViewFactory',
        'Illuminate\Contracts\View\View',
        'Illuminate\Http\RedirectResponse',
        'Illuminate\Routing\Redirector',
        'Illuminate\Support\Collection',
        sprintf('App\Http\Requests\Admin\%s\Destroy%s', $modelWithNamespaceFromDefault, $modelBaseName),
        sprintf('App\Http\Requests\Admin\%s\Index%s', $modelWithNamespaceFromDefault, $modelBaseName),
        sprintf('App\Http\Requests\Admin\%s\Store%s', $modelWithNamespaceFromDefault, $modelBaseName),
        sprintf('App\Http\Requests\Admin\%s\Update%s', $modelWithNamespaceFromDefault, $modelBaseName),
        $modelFullName,
    ];
    if ($export) {
        $uses = array_merge($uses, [
            sprintf('App\Exports\%s', $exportBaseName),
            'Maatwebsite\Excel\Excel',
            'Symfony\Component\HttpFoundation\BinaryFileResponse',
        ]);
    }
    if ($activation) {
        $uses = array_merge($uses, [
            'Brackets\AdminAuth\Activation\Contracts\ActivationBroker',
            'Brackets\AdminAuth\Services\ActivationService',
            'Illuminate\Http\Request',
        ]);
    }

    $belongsToManyRelations = [];
    if (count($relations) > 0 && count($relations['belongsToMany']) > 0) {
        $belongsToManyRelations = $relations['belongsToMany'];
        foreach ($belongsToManyRelations as $belongsToMany) {
            $uses[] = $belongsToMany['related_model'];
        }
    }
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

class {{ $controllerBaseName }} extends Controller
{
    public function __construct(
        public readonly Config $config,
        public readonly Gate $gate,
        public readonly Redirector $redirector,
        public readonly UrlGenerator $urlGenerator,
        public readonly ViewFactory $viewFactory,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Index{{ $modelBaseName }} $request): array|View
    {
        // create and AdminListingService instance for a specific model and
        $data = AdminListingService::create({{ $modelBaseName }}::class)
            ->processRequestAndGet(
                // pass the request with params
                $request,
                // set columns to query
                ['{!! implode('\', \'', $columnsToQuery) !!}'],
                // set columns to searchIn
                ['{!! implode('\', \'', $columnsToSearchIn) !!}'],
            );

        if ($request->ajax()) {
            return [
                'data' => $data,
                'activation' => $this->config->get('admin-auth.activation_enabled'),
            ];
        }

        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.index',
            [
                'data' => $data,
                'url' => $this->urlGenerator->route('admin/{{ $resource }}/index'),
                'createUrl' => $this->urlGenerator->route('admin/{{ $resource }}/create'),
@if($export)
                'exportUrl' => $this->urlGenerator->route('admin/{{ $resource }}/export'),
@endif
                'activation' => $this->config->get('admin-auth.activation_enabled'),
            ],
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * {{'@'}}throws AuthorizationException
     */
    public function create(): View
    {
        $this->gate->authorize('admin.{{ $modelDotNotation }}.create');

        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.create',
            [
                'action' => $this->urlGenerator->to('admin/{{ $resource }}'),
                'activation' => $this->config->get('admin-auth.activation_enabled'),
@foreach($belongsToManyRelations as $belongsToMany)
                '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::all(),
@endforeach
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store{{ $modelBaseName }} $request): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getModifiedData();

        // Store the {{ $modelBaseName }}
@if (count($belongsToManyRelations) > 0)
@foreach($belongsToManyRelations as $belongsToMany)
        ${{ $modelVariableName }} = {{ $modelBaseName }}::create($sanitized);

        // But we do have a {{ $belongsToMany['related_table'] }}, so we need to attach the {{ $belongsToMany['related_table'] }} to the {{ $modelVariableName }}
        ${{ $modelVariableName }}->{{ $belongsToMany['related_table'] }}()->sync((new Collection($request->input('{{ $belongsToMany['related_table'] }}', [])))->map->id->toArray());
@endforeach

@else
        {{ $modelBaseName }}::create($sanitized);

@endif
        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/{{ $resource }}'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/{{ $resource }}');
    }

    /**
     * Display the specified resource.
     *
     * {{'@'}}throws AuthorizationException
     */
    public function show({{ $modelBaseName }} ${{ $modelVariableName }}): void
    {
        $this->gate->authorize('admin.{{ $modelDotNotation }}.show', ${{ $modelVariableName }});

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * {{'@'}}throws AuthorizationException
     */
    public function edit({{ $modelBaseName }} ${{ $modelVariableName }}): View
    {
        $this->gate->authorize('admin.{{ $modelDotNotation }}.edit', ${{ $modelVariableName }});

@if (count($belongsToManyRelations) > 0)
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
                '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::all(),
@endforeach
            ],
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): array|RedirectResponse
    {
        // Sanitize input
        $sanitized = $request->getModifiedData();

        // Update changed values {{ $modelBaseName }}
        ${{ $modelVariableName }}->update($sanitized);

@if (count($belongsToManyRelations) > 0)
@foreach($belongsToManyRelations as $belongsToMany)
        // But we do have a {{ $belongsToMany['related_table'] }}, so we need to attach the {{ $belongsToMany['related_table'] }} to the {{ $modelVariableName }}
        if ($request->input('{{ $belongsToMany['related_table'] }}')) {
            ${{ $modelVariableName }}->{{ $belongsToMany['related_table'] }}()->sync((new Collection($request->input('{{ $belongsToMany['related_table'] }}', [])))->map->id->toArray());
        }
@endforeach
@endif

        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/{{ $resource }}'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->to('admin/{{ $resource }}');
    }

    /**
     * Remove the specified resource from storage.
     *
     * {{'@'}}throws Exception
     */
    public function destroy(Destroy{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): array|RedirectResponse
    {
        ${{ $modelVariableName }}->delete();

        if ($request->ajax()) {
            return ['message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return $this->redirector->back();
    }
@if($activation)

    /**
     * Resend activation e-mail
     *
     * {{'@'}}throws HttpException
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
                return ['message' => trans('brackets/admin-ui::admin.operation.succeeded')];
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
@if($export)

    /**
     * Export entities
     */
    public function export(Excel $excel, {{ $exportBaseName }} $export): ?BinaryFileResponse
    {
        return $excel->download($export, '{{ Str::plural($modelVariableName) }}.xlsx');
    }
@endif}
