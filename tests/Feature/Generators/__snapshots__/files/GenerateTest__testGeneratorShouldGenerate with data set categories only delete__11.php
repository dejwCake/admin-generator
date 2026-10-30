<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\ForceDestroyCategory;
use App\Http\Requests\Admin\Category\RestoreCategory;
use App\Models\Category;
use Closure;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;

final class CategoriesTrashController extends Controller
{
    public function __construct(
        private readonly DatabaseManager $databaseManager,
        private readonly Redirector $redirector,
    ) {
    }

    /**
     * Permanently remove the specified resource, live or trashed, from storage.
     *
     * @throws ValidationException
     */
    public function forceDestroy(ForceDestroyCategory $request, Category $category): array|RedirectResponse
    {
        $this->runInTransaction(
            static fn (): ?bool => $category->forceDelete(),
            'brackets/admin-ui::admin.operation.force_delete_failed',
        );

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }

    /**
     * Restore the specified trashed resource.
     *
     * @throws ValidationException
     */
    public function restore(RestoreCategory $request, Category $category): array|RedirectResponse
    {
        $this->runInTransaction(
            static fn (): bool => $category->restore(),
            'brackets/admin-ui::admin.operation.restore_failed',
        );

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }

    /**
     * Run the operation in a transaction and report an integrity constraint violation (a unique
     * value taken by a live row, a row still referenced by another) as a validation error.
     *
     * @throws ValidationException
     */
    private function runInTransaction(Closure $operation, string $failedMessageKey): void
    {
        try {
            $this->databaseManager->transaction($operation);
        } catch (QueryException $exception) {
            if (!str_starts_with((string) $exception->getCode(), '23')) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'category' => trans($failedMessageKey),
            ]);
        }
    }
}
