<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Feed\Article;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

final class BulkDestroyArticle extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.feed.article.bulk-delete');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ids.*' => [
                'integer',
            ],
        ];
    }

    public function getIds(): Collection
    {
        $data = $this->validated();

        return new Collection($data['ids'] ?? []);
    }
}
