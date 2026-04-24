<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Billing\Cat;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexCat extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.billing.cat.index');
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
                    'name',
                    'first_name',
                    'last_name',
                    'subject',
                    'email',
                    'language',
                    'long_text',
                    'published_at',
                    'published_to',
                    'date_start',
                    'time_start',
                    'date_time_end',
                    'released_at',
                    'text',
                    'description',
                    'enabled',
                    'send',
                    'price',
                    'rating',
                    'views',
                    'created_by_admin_user_id',
                    'updated_by_admin_user_id',
                    'created_at',
                    'updated_at',
                ]),
                'nullable',
            ],
            'orderDirection' => [
                Rule::in([
                    'asc',
                    'desc',
                ]),
                'nullable',
            ],
            'search' => [
                'string',
                'nullable',
            ],
            'page' => [
                'integer',
                'nullable',
            ],
            'per_page' => [
                'integer',
                'nullable',
            ],
        ];
    }
}
