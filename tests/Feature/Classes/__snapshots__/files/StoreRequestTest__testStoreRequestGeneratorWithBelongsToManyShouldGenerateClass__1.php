<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Category;

use Brackets\Translatable\Http\Requests\TranslatableFormRequest;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Validation\Rule;

class StoreCategory extends TranslatableFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.category.create');
    }

    /**
     * Get the validation rules that apply to the requests untranslatable fields.
     */
    public function untranslatableRules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'title' => ['required', 'string'],
            'slug' => ['required', Rule::unique('categories', 'slug'), 'string'],
            'perex' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'date_start' => ['nullable', 'date'],
            'time_start' => ['nullable', 'date_format:H:i:s'],
            'date_time_end' => ['nullable', 'date'],
            'enabled' => ['required', 'boolean'],
            'send' => ['required', 'boolean'],
            'price' => ['nullable', 'numeric'],
            'views' => ['required', 'integer'],
            'created_by_admin_user_id' => ['nullable', 'integer'],
            'updated_by_admin_user_id' => ['nullable', 'integer'],

            'posts' => ['array'],
        ];
    }

    /**
     * Get the validation rules that apply to the requests translatable fields.
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function translatableRules(string $locale): array
    {
        return [
            'text' => ['required', 'string'],
            'description' => ['required', 'string'],
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
