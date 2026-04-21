<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Feed\Article;

use App\Models\Feed\Article;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property Article $article
 */
final class ImpersonalLoginArticle extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.feed.article.impersonal-login', $this->article);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
