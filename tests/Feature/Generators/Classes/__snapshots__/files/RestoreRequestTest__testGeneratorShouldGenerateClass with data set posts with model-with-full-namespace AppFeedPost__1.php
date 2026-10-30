<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Post;

use App\Feed\Post;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * @property Post $post
 */
final class RestorePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.post.restore', $this->post);
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
                if (!$this->post->isRestorable()) {
                    $validator->errors()->add(
                        'post',
                        trans('brackets/admin-ui::admin.operation.not_restorable'),
                    );
                }
            },
        ];
    }
}
