@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};

@if($export)
use App\Exports\{{$exportBaseName}};
@endif
use App\Http\Controllers\Controller;
@if(!$withoutBulk)
use App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }}\BulkDestroy{{ $modelBaseName }};
@endif
use App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }}\Destroy{{ $modelBaseName }};
use App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }}\Index{{ $modelBaseName }};
use App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }}\Store{{ $modelBaseName }};
use App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }}\Update{{ $modelBaseName }};
use {{ $modelFullName }};
use Brackets\AdminListing\Facades\AdminListing;
@if(!$withoutBulk && $hasSoftDelete)
use Carbon\Carbon;
@endif
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
@if (count($relations))
@if (count($relations['belongsToMany']))
@foreach($relations['belongsToMany'] as $belongsToMany)
use {{ $belongsToMany['related_model'] }};
@endforeach
@endif
@endif
@if(!$withoutBulk)
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
@endif
@if(in_array('created_by_admin_user_id', $columnsToQuery) || in_array('updated_by_admin_user_id', $columnsToQuery))
use Illuminate\Support\Facades\Auth;
@endif
@if($export)use Maatwebsite\Excel\Facades\Excel;
@endif
@if($export)use Symfony\Component\HttpFoundation\BinaryFileResponse;
@endif
use Illuminate\View\View;

class {{ $controllerBaseName }} extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Index{{ $modelBaseName }} $request): Response|View
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create({{ $modelBaseName }}::class)->processRequestAndGet(
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
            }
    @elseif(in_array('created_by_admin_user_id', $columnsToQuery))
        function ($query) use ($request) {
                $query->with(['createdByAdminUser']);
            }
    @elseif(in_array('updated_by_admin_user_id', $columnsToQuery))
        function ($query) use ($request) {
                $query->with(['updatedByAdminUser']);
            }
    @endif
@endif()
        );

        if ($request->ajax()) {
@if(!$withoutBulk)
            if ($request->has('bulk')) {
                return new Response([
                    'bulkItems' => $data->pluck('id'),
                ]);
            }
@endif

            return new Response(['data' => $data]);
        }

        return view('admin.{{ $modelDotNotation }}.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * {{'@'}}throws AuthorizationException
     */
    public function create(): View
    {
        $this->authorize('admin.{{ $modelDotNotation }}.create');

@if (count($relations) && count($relations['belongsToMany']))
        return view('admin.{{ $modelDotNotation }}.create',[
@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::all(),
@endforeach
        ]);
@else
        return view('admin.{{ $modelDotNotation }}.create');
@endif
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store{{ $modelBaseName }} $request): RedirectResponse|Response
    {
        $data = $request->getValidated();
@if(in_array('created_by_admin_user_id', $columnsToQuery) || in_array('updated_by_admin_user_id', $columnsToQuery))
    @if(in_array('created_by_admin_user_id', $columnsToQuery) && in_array('updated_by_admin_user_id', $columnsToQuery))
    $data['created_by_admin_user_id'] = Auth::getUser()->id;
        $data['updated_by_admin_user_id'] = Auth::getUser()->id;
    @elseif(in_array('created_by_admin_user_id', $columnsToQuery))
        $data['created_by_admin_user_id'] = Auth::getUser()->id;
    @elseif(in_array('updated_by_admin_user_id', $columnsToQuery))
        $data['updated_by_admin_user_id'] = Auth::getUser()->id;
    @endif
@endif()

        // Store the {{ $modelBaseName }}
@if (count($relations))
@if (count($relations['belongsToMany']))
        ${{ $modelVariableName }} = {{ $modelBaseName }}::create($data);

@foreach($relations['belongsToMany'] as $belongsToMany)
        // But we do have a {{ $belongsToMany['related_table'] }}, so we need to attach the {{ $belongsToMany['related_table'] }} to the {{ $modelVariableName }}
        ${{ $modelVariableName }}->{{ $belongsToMany['related_table'] }}()->sync(collect($request->input('{{ $belongsToMany['related_table'] }}', []))->map->id->toArray());
@endforeach

@else
        {{ $modelBaseName }}::create($data);

@endif
@endif
        if ($request->ajax()) {
            return new Response([
                'redirect' => route('admin/{{ $resource }}/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ]);
        }

        return redirect()->route('admin/{{ $resource }}/index');
    }

    /**
     * Display the specified resource.
     *
     * {{'@'}}throws AuthorizationException
     */
    public function show({{ $modelBaseName }} ${{ $modelVariableName }}): void
    {
        $this->authorize('admin.{{ $modelDotNotation }}.show', ${{ $modelVariableName }});

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * {{'@'}}throws AuthorizationException
     */
    public function edit({{ $modelBaseName }} ${{ $modelVariableName }}): View
    {
        $this->authorize('admin.{{ $modelDotNotation }}.edit', ${{ $modelVariableName }});

@if(in_array('created_by_admin_user_id', $columnsToQuery) || in_array('updated_by_admin_user_id', $columnsToQuery))
    @if(in_array('created_by_admin_user_id', $columnsToQuery) && in_array('updated_by_admin_user_id', $columnsToQuery))
    ${{ $modelVariableName }}->load(['createdByAdminUser', 'updatedByAdminUser']);
    @elseif(in_array('created_by_admin_user_id', $columnsToQuery))
    ${{ $modelVariableName }}->load('createdByAdminUser');
    @elseif(in_array('updated_by_admin_user_id', $columnsToQuery))
    ${{ $modelVariableName }}->load('updatedByAdminUser');
    @endif
@endif()

@if (count($relations))
@if (count($relations['belongsToMany']))
@foreach($relations['belongsToMany'] as $belongsToMany)
        ${{ $modelVariableName }}->load('{{ $belongsToMany['related_table'] }}');
@endforeach

@endif
@endif
        return view(
            'admin.{{ $modelDotNotation }}.edit',
            [
                '{{ $modelVariableName }}' => ${{ $modelVariableName }},
@if (count($relations))
@if (count($relations['belongsToMany']))
@foreach($relations['belongsToMany'] as $belongsToMany)
                '{{ $belongsToMany['related_table'] }}' => {{ $belongsToMany['related_model_name'] }}::all(),
@endforeach
@endif
@endif
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): RedirectResponse|Response
    {
        $data = $request->getValidated();
@if(in_array('updated_by_admin_user_id', $columnsToQuery))
        $data['updated_by_admin_user_id'] = Auth::getUser()->id;
@endif

        // Update changed values {{ $modelBaseName }}
        ${{ $modelVariableName }}->update($data);

@if (count($relations))
@if (count($relations['belongsToMany']))
@foreach($relations['belongsToMany'] as $belongsToMany)
        // But we do have a {{ $belongsToMany['related_table'] }}, so we need to attach the {{ $belongsToMany['related_table'] }} to the {{ $modelVariableName }}
        if ($request->has('{{ $belongsToMany['related_table'] }}')) {
            ${{ $modelVariableName }}->{{ $belongsToMany['related_table'] }}()->sync(collect($request->input('{{ $belongsToMany['related_table'] }}', []))->map->id->toArray());
        }
@endforeach

@endif
@endif
        if ($request->ajax()) {
            return new Response([
                'redirect' => route('admin/{{ $resource }}/index'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
@if($containsPublishedAtColumn)
                'object' => ${{ $modelVariableName }}
@endif
            ]);
        }

        return redirect()->route('admin/{{ $resource }}/index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * {{'@'}}throws Exception
     */
    public function destroy(Destroy{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): RedirectResponse|Response
    {
        ${{ $modelVariableName }}->delete();

        if ($request->ajax()) {
            return new Response(
                ['message' => trans('brackets/admin-ui::admin.operation.succeeded')],
            );
        }

        return redirect()->back();
    }

    @if(!$withoutBulk)/**
     * Remove the specified resources from storage.
     *
     * {{'@'}}throws Exception
     */
    public function bulkDestroy(BulkDestroy{{ $modelBaseName }} $request): RedirectResponse|Response
    {
@if($hasSoftDelete)
        DB::transaction(static function () use ($request) {
            (new Collection($request->getIds()))
                ->chunk(1000)
                ->each(static function ($bulkChunk): void {
                    DB::table('{{ str_plural($modelVariableName) }}')
                        ->whereIn('id', $bulkChunk)
                        ->update([
                            'deleted_at' => Carbon::now()
                        ]);

                    // TODO your code goes here
                });
        });
@else
        DB::transaction(static function () use ($request) {
            (new Collection($request->getIds()))
                ->chunk(1000)
                ->each(static function ($bulkChunk): void {
                    {{ $modelBaseName }}::whereIn('id', $bulkChunk)
                        ->delete();

                    // TODO your code goes here
                });
        });
@endif

        return new Response(
            ['message' => trans('brackets/admin-ui::admin.operation.succeeded')]
        );
    }
@endif
@if($export)

    /**
     * Export entities
     */
    public function export(): ?BinaryFileResponse
    {
        return Excel::download(app({{ $exportBaseName }}::class), '{{ str_plural($modelVariableName) }}.xlsx');
    }
@endif}
