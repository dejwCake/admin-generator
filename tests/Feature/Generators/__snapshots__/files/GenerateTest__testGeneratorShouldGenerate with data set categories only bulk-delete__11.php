<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\BulkForceDestroyCategory;
use App\Http\Requests\Admin\Category\BulkRestoreCategory;
use App\Models\Category;
use Closure;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class CategoriesTrashController extends Controller
{
    public function __construct(
        private readonly DatabaseManager $databaseManager,
        private readonly Redirector $redirector,
    ) {
    }

    /**
     * Permanently remove the specified resources, live or trashed, from storage.
     *
     * @throws ValidationException
     */
    public function bulkForceDestroy(BulkForceDestroyCategory $request): array|RedirectResponse
    {
        $this->runInTransaction(
            static function () use ($request): void {
                $request->getIds()
                    ->chunk(1000)
                    ->each(static function (Collection $bulkChunk): void {
                        Category::withTrashed()
                            ->whereIn('id', $bulkChunk)
                            ->get()
                            ->each(static fn (Category $category): ?bool => $category->forceDelete());
                    });
            },
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
     * Restore the specified trashed resources, skipping those that belong to a missing or trashed row.
     *
     * @throws ValidationException
     */
    public function bulkRestore(BulkRestoreCategory $request): array|RedirectResponse
    {
        [$restorable, $skipped] = $request->getIds()
            ->chunk(1000)
            ->flatMap(static fn (Collection $bulkChunk): Collection => Category::onlyTrashed()
                ->whereIn('id', $bulkChunk)
                ->get()
                ->toBase())
            ->partition(static fn (Category $category): bool => $category->isRestorable());

        $this->runInTransaction(
            static function () use ($restorable): void {
                $restorable->each(static fn (Category $category): bool => $category->restore());
            },
            'brackets/admin-ui::admin.operation.restore_failed',
        );

        $message = $skipped->isEmpty()
            ? trans('brackets/admin-ui::admin.operation.succeeded')
            : trans('brackets/admin-ui::admin.operation.restore_skipped', ['count' => $skipped->count()]);

        if ($request->ajax()) {
            return [
                'message' => $message,
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
