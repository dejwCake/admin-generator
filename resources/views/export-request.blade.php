@php echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $classNamespace }};

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

final class {{ $classBaseName }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.{{ $modelDotNotation }}.index');
    }
}
