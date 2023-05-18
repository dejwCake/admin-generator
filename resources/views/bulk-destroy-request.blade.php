@php echo "<?php"
@endphp


declare(strict_types=1);

namespace App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }};

use Brackets\AdminUI\Http\Requests\Traits\Validated;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class BulkDestroy{{ $modelBaseName }} extends FormRequest
{
    use Validated;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('admin.{{ $modelDotNotation }}.bulk-delete');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * {{'@'}}return array{{'<'}}string, string>
     */
    public function rules(): array
    {
        return [
            'ids.*' => 'integer',
        ];
    }

    /**
     * {{'@'}}return array{{'<'}}int>
     */
    public function getIds(): array
    {
        $data = $this->getValidated();

        return $data['ids'];
    }
}
