@php echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $classNamespace }};
@php
    $uses = [
        'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Foundation\Http\FormRequest',
        $modelFullName,
    ];
    $uses = Arr::sort($uses);
@endphp

@foreach($uses as $use)
use {{ $use }};
@endforeach

/**
 * @property {{ $modelBaseName }} ${{ $modelVariableName }}
 */
class {{ $classBaseName }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.{{ $modelDotNotation }}.delete', $this->{{ $modelVariableName }});
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
