<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

class IndexUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.user.index');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'orderBy' => [
                'in:id,name,email,email_verified_at',
                'nullable',
            ],
            'orderDirection' => ['in:asc,desc', 'nullable'],
            'search' => ['string', 'nullable'],
            'page' => ['integer', 'nullable'],
            'per_page' => ['integer', 'nullable'],
        ];
    }
}
