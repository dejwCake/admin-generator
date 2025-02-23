<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Cat;

use App\Billing\Cat;
use Brackets\Translatable\Http\Requests\TranslatableFormRequest;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Validation\Rule;

/**
 * @property Cat $cat
 */
class UpdateCat extends TranslatableFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.cat.edit', $this->cat);
    }

    /**
     * Get the validation rules that apply to the requests untranslatable fields.
     */
    public function untranslatableRules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'title' => ['sometimes', 'string'],
            'slug' => ['sometimes', Rule::unique('categories', 'slug')
                ->ignore($this->cat->getKey(), $this->cat->getKeyName()), 'string'],
            'perex' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'date_start' => ['nullable', 'date'],
            'time_start' => ['nullable', 'date_format:H:i:s'],
            'date_time_end' => ['nullable', 'date'],
            'enabled' => ['sometimes', 'boolean'],
            'send' => ['sometimes', 'boolean'],
            'price' => ['nullable', 'numeric'],
            'views' => ['sometimes', 'integer'],
            'created_by_admin_user_id' => ['nullable', 'integer'],
            'updated_by_admin_user_id' => ['nullable', 'integer'],
            'publish_now' => ['nullable', 'boolean'],
            'unpublish_now' => ['nullable', 'boolean'],
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
            'text' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
        ];
    }

    /**
     * Modify input data
     */
    public function getSanitized(): array
    {
        $sanitized = $this->validated();

        if (isset($sanitized['publish_now']) && $sanitized['publish_now'] === true) {
            $sanitized['published_at'] = CarbonImmutable::now();
        }

        if (isset($sanitized['unpublish_now']) && $sanitized['unpublish_now'] === true) {
            $sanitized['published_at'] = null;
        }

        //Add your code for manipulation with request data here

        return $sanitized;
    }
}
