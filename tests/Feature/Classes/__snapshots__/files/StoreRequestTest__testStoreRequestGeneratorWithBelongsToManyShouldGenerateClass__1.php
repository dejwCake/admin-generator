<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Category;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategory extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.category.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],

            'posts' => ['array'],
        ];
    }

    /**
     * Modify input data
     */
    public function getSanitized(): array
    {
        //phpcs:ignore SlevomatCodingStandard.Variables.UselessVariable.UselessVariable
        $sanitized = $this->validated();

        //Add your code for manipulation with request data here

        return $sanitized;
    }
}
