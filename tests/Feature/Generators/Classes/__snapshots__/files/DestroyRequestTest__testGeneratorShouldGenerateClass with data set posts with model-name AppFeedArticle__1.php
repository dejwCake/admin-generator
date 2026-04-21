<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Article;

use App\Feed\Article;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property Article $article
 */
final class DestroyArticle extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.article.delete', $this->article);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
