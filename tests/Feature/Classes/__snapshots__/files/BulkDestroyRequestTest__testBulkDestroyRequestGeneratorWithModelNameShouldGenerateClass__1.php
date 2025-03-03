<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Billing\Cat;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyCat extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.billing.cat.bulk-delete');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ids.*' => 'integer',
        ];
    }
}
