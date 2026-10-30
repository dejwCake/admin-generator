<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Feed\Article;

use App\Models\Feed\Article;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * @property Article $article
 */
final class RestoreArticle extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.feed.article.restore', $this->article);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Refuse to restore a row that belongs to a missing or trashed row.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (!$this->article->isRestorable()) {
                    $validator->errors()->add(
                        'article',
                        trans('brackets/admin-ui::admin.operation.not_restorable'),
                    );
                }
            },
        ];
    }
}
