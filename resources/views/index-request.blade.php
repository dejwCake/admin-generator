@php echo "<?php"
@endphp


declare(strict_types=1);

namespace App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;


class Index{{ $modelBaseName }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('admin.{{ $modelDotNotation }}.index');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * {{'@'}}return array{{'<'}}string, string|In>
     */
    public function rules(): array
    {
        return [
            'orderBy' => ['nullable', Rule::in(['{{ implode('\', \'', $columnsToQuery) }}']),],
            'orderDirection' => ['nullable', Rule::in(['asc', 'desc']),],
            'search' => ['nullable', 'string',],
            'page' => ['nullable', 'integer',],
            'per_page' => ['nullable', 'integer',],
        ];
    }
}
