<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\AdminUser;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAdminUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.admin-user.index');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'orderBy' => [
                Rule::in([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'activated',
                    'forbidden',
                    'language',
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
