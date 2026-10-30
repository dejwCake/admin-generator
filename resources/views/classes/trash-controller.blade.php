@php
    use Illuminate\Support\Collection;
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $controllerNamespace }};
@php
    $uses = new Collection([
        'App\Http\Controllers\Controller',
        'Closure',
        'Illuminate\Database\DatabaseManager',
        'Illuminate\Database\QueryException',
        'Illuminate\Http\RedirectResponse',
        'Illuminate\Routing\Redirector',
        'Illuminate\Validation\ValidationException',
        $modelFullName,
    ]);
    if ($hasDelete) {
        $uses->push(sprintf('App\Http\Requests\Admin\%s\ForceDestroy%s', $modelWithNamespaceFromDefault, $modelBaseName));
        $uses->push(sprintf('App\Http\Requests\Admin\%s\Restore%s', $modelWithNamespaceFromDefault, $modelBaseName));
    }
    if ($hasBulkDelete) {
        $uses->push(sprintf('App\Http\Requests\Admin\%s\BulkForceDestroy%s', $modelWithNamespaceFromDefault, $modelBaseName));
        $uses->push(sprintf('App\Http\Requests\Admin\%s\BulkRestore%s', $modelWithNamespaceFromDefault, $modelBaseName));
        $uses->push('Illuminate\Support\Collection');
    }
    $uses = $uses->unique()->sort();
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

final class {{ $controllerBaseName }} extends Controller
{
    public function __construct(
        private readonly DatabaseManager $databaseManager,
        private readonly Redirector $redirector,
    ) {
    }
@if($hasDelete)

    /**
     * Permanently remove the specified resource, live or trashed, from storage.
     *
     * @throws ValidationException
     */
    public function forceDestroy(ForceDestroy{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): array|RedirectResponse
    {
        $this->runInTransaction(
            static fn (): ?bool => ${{ $modelVariableName }}->forceDelete(),
            'brackets/admin-ui::admin.operation.force_delete_failed',
        );

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }
@endif
@if($hasBulkDelete)

    /**
     * Permanently remove the specified resources, live or trashed, from storage.
     *
     * @throws ValidationException
     */
    public function bulkForceDestroy(BulkForceDestroy{{ $modelBaseName }} $request): array|RedirectResponse
    {
        $this->runInTransaction(
            static function () use ($request): void {
                $request->getIds()
                    ->chunk(1000)
                    ->each(static function (Collection $bulkChunk): void {
                        {{ $modelBaseName }}::withTrashed()
                            ->whereIn('id', $bulkChunk)
                            ->get()
                            ->each(static fn ({{ $modelBaseName }} ${{ $modelVariableName }}): ?bool => ${{ $modelVariableName }}->forceDelete());
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
@endif
@if($hasDelete)

    /**
     * Restore the specified trashed resource.
     *
     * @throws ValidationException
     */
    public function restore(Restore{{ $modelBaseName }} $request, {{ $modelBaseName }} ${{ $modelVariableName }}): array|RedirectResponse
    {
        $this->runInTransaction(
            static fn (): bool => ${{ $modelVariableName }}->restore(),
            'brackets/admin-ui::admin.operation.restore_failed',
        );

        if ($request->ajax()) {
            return [
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return $this->redirector->back();
    }
@endif
@if($hasBulkDelete)

    /**
     * Restore the specified trashed resources, skipping those that belong to a missing or trashed row.
     *
     * @throws ValidationException
     */
    public function bulkRestore(BulkRestore{{ $modelBaseName }} $request): array|RedirectResponse
    {
        [$restorable, $skipped] = $request->getIds()
            ->chunk(1000)
            ->flatMap(static fn (Collection $bulkChunk): Collection => {{ $modelBaseName }}::onlyTrashed()
                ->whereIn('id', $bulkChunk)
                ->get()
                ->toBase())
            ->partition(static fn ({{ $modelBaseName }} ${{ $modelVariableName }}): bool => ${{ $modelVariableName }}->isRestorable());

        $this->runInTransaction(
            static function () use ($restorable): void {
                $restorable->each(static fn ({{ $modelBaseName }} ${{ $modelVariableName }}): bool => ${{ $modelVariableName }}->restore());
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
@endif

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
                '{{ $modelVariableName }}' => trans($failedMessageKey),
            ]);
        }
    }
}
