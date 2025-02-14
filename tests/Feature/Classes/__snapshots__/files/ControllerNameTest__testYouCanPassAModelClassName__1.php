<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cat\BulkDestroyCat;
use App\Http\Requests\Admin\Cat\DestroyCat;
use App\Http\Requests\Admin\Cat\IndexCat;
use App\Http\Requests\Admin\Cat\StoreCat;
use App\Http\Requests\Admin\Cat\UpdateCat;
use App\Billing\Cat;
use Brackets\AdminListing\Facades\AdminListing;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoriesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexCat $request
     * @return array|Factory|View
     */
    public function index(IndexCat $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Cat::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'title'],

            // set columns to searchIn
            ['id']
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.cat.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('admin.cat.create');

        return view('admin.cat.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCat $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StoreCat $request)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Store the Cat
        $cat = Cat::create($sanitized);

        if ($request->ajax()) {
            return ['redirect' => url('admin/cats'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/cats');
    }

    /**
     * Display the specified resource.
     *
     * @param Cat $cat
     * @throws AuthorizationException
     * @return void
     */
    public function show(Cat $cat)
    {
        $this->authorize('admin.cat.show', $cat);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Cat $cat
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Cat $cat)
    {
        $this->authorize('admin.cat.edit', $cat);


        return view('admin.cat.edit', [
            'cat' => $cat,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCat $request
     * @param Cat $cat
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdateCat $request, Cat $cat)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Update changed values Cat
        $cat->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/cats'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/cats');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyCat $request
     * @param Cat $cat
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyCat $request, Cat $cat)
    {
        $cat->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyCat $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyCat $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Cat::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }
}
