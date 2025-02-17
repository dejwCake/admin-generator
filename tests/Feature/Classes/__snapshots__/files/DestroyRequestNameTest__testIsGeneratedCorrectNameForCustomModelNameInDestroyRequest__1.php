<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Billing\Cat;

use App\Models\Billing\Cat;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property Cat $cat
 */
class DestroyCat extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.billing.cat.delete', $this->cat);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
