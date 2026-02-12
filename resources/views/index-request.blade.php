@php echo "<?php"
@endphp


declare(strict_types=1);

namespace {{ $classNamespace }};

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class {{ $classBaseName }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.{{ $modelDotNotation }}.index');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'orderBy' => [
                Rule::in([
                    '{!! implode("',\n                    '", $columnsToQuery) !!}',
                ]),
                'nullable',
            ],
            'orderDirection' => [Rule::in(['asc', 'desc']), 'nullable'],
            'search' => ['string', 'nullable'],
            'page' => ['integer', 'nullable'],
            'per_page' => ['integer', 'nullable'],
        ];
    }
}
