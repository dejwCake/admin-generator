<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Cat;

use App\Billing\Cat;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * @property Cat $cat
 */
final class RestoreCat extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.cat.restore', $this->cat);
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
                if (!$this->cat->isRestorable()) {
                    $validator->errors()->add(
                        'cat',
                        trans('brackets/admin-ui::admin.operation.not_restorable'),
                    );
                }
            },
        ];
    }
}
