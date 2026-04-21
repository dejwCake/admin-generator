<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Article;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexArticle extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.article.index');
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
                    'title',
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
