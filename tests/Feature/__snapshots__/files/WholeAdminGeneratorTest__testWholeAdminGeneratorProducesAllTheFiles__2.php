<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Category;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCategory extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.category.index');
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
                    'user_id',
                    'title',
                    'published_at',
                    'date_start',
                    'time_start',
                    'date_time_end',
                    'text',
                    'description',
                    'enabled',
                    'send',
                    'price',
                    'views',
                    'created_by_admin_user_id',
                    'updated_by_admin_user_id',
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
