@php
    use Illuminate\Support\Collection;
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $classNamespace }};
@php
    $uses = new Collection([
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Foundation\Http\FormRequest',
        'Illuminate\Validation\Validator',
        $modelFullName,
    ]);
    $uses = $uses->unique()->sort();
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

/**
 * @property {{ $modelBaseName }} ${{ $modelVariableName }}
 */
final class {{ $classBaseName }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.{{ $modelDotNotation }}.restore', $this->{{ $modelVariableName }});
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Refuse to restore a row that belongs to a missing or trashed row.
     *
     * {{'@'}}return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (!$this->{{ $modelVariableName }}->isRestorable()) {
                    $validator->errors()->add(
                        '{{ $modelVariableName }}',
                        trans('brackets/admin-ui::admin.operation.not_restorable'),
                    );
                }
            },
        ];
    }
}
