@php use Illuminate\Support\Arr;use Illuminate\Support\Str;echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};
@php
    $uses = [
        'App\Http\Controllers\Controller',
        'Brackets\AdminListing\Services\AdminListingService',
        'Exception',
        'Illuminate\Auth\Access\AuthorizationException',
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

    $belongsToManyRelations = [];
    if (count($relations) > 0 && count($relations['belongsToMany']) > 0) {
        $belongsToManyRelations = $relations['belongsToMany'];
        foreach ($belongsToManyRelations as $belongsToMany) {
            $uses[] = $belongsToMany['related_model'];
        }
    }
    if (!$withoutBulk) {
        $uses = array_merge($uses, [
            sprintf('App\Http\Requests\Admin\%s\BulkDestroy%s', $modelWithNamespaceFromDefault, $modelBaseName),
            'Illuminate\Database\DatabaseManager',
        ]);
    }
    if (!$withoutBulk && $hasSoftDelete) {
        $uses[] = 'Carbon\CarbonImmutable';

    }
    if (in_array('created_by_admin_user_id', $columnsToQuery) || in_array('updated_by_admin_user_id', $columnsToQuery)) {
        $uses[] = 'Illuminate\Auth\SessionGuard';
    }
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

class {{ $controllerBaseName }} extends Controller
{
    public function __construct(
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
@if(in_array('created_by_admin_user_id', $columnsToQuery) || in_array('updated_by_admin_user_id', $columnsToQuery))
    @if(in_array('created_by_admin_user_id', $columnsToQuery) && in_array('updated_by_admin_user_id', $columnsToQuery))
            function ($query) use ($request) {
                    $query->with(['createdByAdminUser', 'updatedByAdminUser']);
                },
    @elseif(in_array('created_by_admin_user_id', $columnsToQuery))
            function ($query) use ($request) {
                    $query->with(['createdByAdminUser']);
                },
    @elseif(in_array('updated_by_admin_user_id', $columnsToQuery))
            function ($query) use ($request) {
                    $query->with(['updatedByAdminUser']);
                },
    @endif
@endif()
            );

        if ($request->ajax()) {
@if(!$withoutBulk)
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
@endif
            return ['data' => $data];
        }

        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.index',
            ['data' => $data],
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
        $sanitized = $request->getSanitized();
@if(in_array('created_by_admin_user_id', $columnsToQuery) || in_array('updated_by_admin_user_id', $columnsToQuery))
    @if(in_array('created_by_admin_user_id', $columnsToQuery) && in_array('updated_by_admin_user_id', $columnsToQuery))
    $sanitized['created_by_admin_user_id'] = $request->user()->id;
        $sanitized['updated_by_admin_user_id'] = $request->user()->id;
    @elseif(in_array('created_by_admin_user_id', $columnsToQuery))
        $sanitized['created_by_admin_user_id'] = $request->user()->id;
    @elseif(in_array('updated_by_admin_user_id', $columnsToQuery))
        $sanitized['updated_by_admin_user_id'] = $request->user()->id;
    @endif
@endif()

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
                'message' => __('brackets/admin-ui::admin.operation.succeeded'),
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

@if(in_array('created_by_admin_user_id', $columnsToQuery) || in_array('updated_by_admin_user_id', $columnsToQuery))
    @if(in_array('created_by_admin_user_id', $columnsToQuery) && in_array('updated_by_admin_user_id', $columnsToQuery))
    ${{ $modelVariableName }}->load(['createdByAdminUser', 'updatedByAdminUser']);
    @elseif(in_array('created_by_admin_user_id', $columnsToQuery))
    ${{ $modelVariableName }}->load('createdByAdminUser');
    @elseif(in_array('updated_by_admin_user_id', $columnsToQuery))
    ${{ $modelVariableName }}->load('updatedByAdminUser');
    @endif

@endif
@if (count($belongsToManyRelations) > 0)
@foreach($belongsToManyRelations as $belongsToMany)
        ${{ $modelVariableName }}->load('{{ $belongsToMany['related_table'] }}');
@endforeach

@endif
        return $this->viewFactory->make(
            'admin.{{ $modelDotNotation }}.edit',
            [
                '{{ $modelVariableName }}' => ${{ $modelVariableName }},
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
        $sanitized = $request->getSanitized();
@if(in_array('updated_by_admin_user_id', $columnsToQuery))
        $sanitized['updated_by_admin_user_id'] = $request->user()->id;
@endif

        // Update changed values {{ $modelBaseName }}
        ${{ $modelVariableName }}->update($sanitized);

@if (count($belongsToManyRelations) > 0)
@foreach($belongsToManyRelations as $belongsToMany)
        // But we do have a {{ $belongsToMany['related_table'] }}, so we need to attach the {{ $belongsToMany['related_table'] }} to the {{ $modelVariableName }}
        if ($request->has('{{ $belongsToMany['related_table'] }}')) {
            ${{ $modelVariableName }}->{{ $belongsToMany['related_table'] }}()->sync((new Collection($request->input('{{ $belongsToMany['related_table'] }}', [])))->map->id->toArray());
        }
@endforeach

@endif
        if ($request->ajax()) {
            return [
                'redirect' => $this->urlGenerator->to('admin/{{ $resource }}'),
                'message' => __('brackets/admin-ui::admin.operation.succeeded'),
@if($containsPublishedAtColumn)
                'object' => ${{ $modelVariableName }},
@endif
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
            return ['message' => __('brackets/admin-ui::admin.operation.succeeded')];
        }

        return $this->redirector->back();
    }

    @if(!$withoutBulk)/**
     * Remove the specified resources from storage.
     *
     * {{'@'}}throws Exception
     */
    public function bulkDestroy(BulkDestroy{{ $modelBaseName }} $request, DatabaseManager $databaseManager): array|RedirectResponse
    {
@if($hasSoftDelete)
        $databaseManager->transaction(static function () use ($request, $databaseManager) {
            (new Collection($request->data['ids']))
                ->chunk(1000)
                ->each(static function ($bulkChunk) use ($databaseManager) {
                    $databaseManager->table('{{ Str::plural($modelVariableName) }}')
                        ->whereIn('id', $bulkChunk)
                        ->update([
                            'deleted_at' => CarbonImmutable::now(),
                        ]);

                    // TODO your code goes here
                });
        });
@else
        $databaseManager->transaction(static function () use ($request, $databaseManager) {
            (new Collection($request->data['ids']))
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    {{ $modelBaseName }}::whereIn('id', $bulkChunk)
                        ->delete();

                    // TODO your code goes here
                });
        });
@endif

        if ($request->ajax()) {
            return ['message' => __('brackets/admin-ui::admin.operation.succeeded')];
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
