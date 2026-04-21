<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Post;

use App\Feed\Post;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

/**
 * @property Post $post
 */
final class UpdatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.post.edit', $this->post);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'string',
            ],

            'categories' => [
                'sometimes',
                'array',
            ],
            'categories.*.id' => [
                'required',
                'integer',
            ],
        ];
    }

    /**
     * Modify input data
     */
    public function getModifiedData(): array
    {
        $data = $this->validated();
        if (isset($data['categories'])) {
            $data['categories'] = new Collection($data['categories'] ?? []);
        }

        //Add your code for manipulation with request data here

        return $data;
    }

    public function getCategoryIds(): ?Collection
    {
        $data = $this->getModifiedData();
        if (!isset($data['categories'])) {
            return null;
        }

        return $data['categories']->pluck('id');
    }
}
