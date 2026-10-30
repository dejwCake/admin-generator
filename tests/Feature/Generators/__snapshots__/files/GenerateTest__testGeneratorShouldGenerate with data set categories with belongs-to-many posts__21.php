<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Category;

use App\Models\Category;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * @property Category $category
 */
final class RestoreCategory extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.category.restore', $this->category);
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
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (!$this->category->isRestorable()) {
                    $validator->errors()->add(
                        'category',
                        trans('brackets/admin-ui::admin.operation.not_restorable'),
                    );
                }
            },
        ];
    }
}
