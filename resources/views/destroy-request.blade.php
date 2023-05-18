@php echo "<?php"
@endphp


declare(strict_types=1);

namespace App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * @property {{ $modelBaseName }} ${{ $modelVariableName }}
 */
class Destroy{{ $modelBaseName }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('admin.{{ $modelDotNotation }}.delete', $this->{{ $modelVariableName }});
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * {{'@'}}return array{{'<'}}string, string>
     */
    public function rules(): array
    {
        return [];
    }
}
