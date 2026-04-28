<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Cat;

use Brackets\Translatable\Http\Requests\TranslatableFormRequest;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreCat extends TranslatableFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.cat.create');
    }

    /**
     * Get the validation rules that apply to the requests untranslatable fields.
     */
    public function untranslatableRules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
            ],
            'title' => [
                'required',
                Rule::unique('categories', 'title'),
                'string',
            ],
            'name' => [
                'nullable',
                'string',
            ],
            'first_name' => [
                'nullable',
                'string',
            ],
            'last_name' => [
                'nullable',
                'string',
            ],
            'subject' => [
                'nullable',
                'string',
            ],
            'email' => [
                'nullable',
                'email',
                'string',
            ],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'string',
            ],
            'language' => [
                'required',
                'string',
            ],
            'slug' => [
                'required',
                Rule::unique('categories', 'slug'),
                'string',
            ],
            'perex' => [
                'nullable',
                'string',
            ],
            'long_text' => [
                'nullable',
                'string',
            ],
            'published_at' => [
                'nullable',
                'date',
            ],
            'published_to' => [
                'nullable',
                'date',
            ],
            'date_start' => [
                'nullable',
                'date',
            ],
            'time_start' => [
                'nullable',
                Rule::date()->format('H:i:s'),
            ],
            'date_time_end' => [
                'nullable',
                'date',
            ],
            'released_at' => [
                'required',
                'date',
            ],
            'enabled' => [
                'required',
                'boolean',
            ],
            'send' => [
                'required',
                'boolean',
            ],
            'price' => [
                'nullable',
                'numeric',
            ],
            'rating' => [
                'nullable',
                'numeric',
            ],
            'views' => [
                'required',
                'integer',
            ],

            'posts' => [
                'array',
            ],
            'posts.*.id' => [
                'required',
                'integer',
            ],
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
            'text' => [
                'required',
                'string',
            ],
            'description' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Modify input data
     */
    public function getModifiedData(): array
    {
        $data = $this->validated();
        $data['posts'] = new Collection($data['posts'] ?? []);

        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        $config = Container::getInstance()->make(Config::class);
        assert($config instanceof Config);
        $adminUserGuard = $config->get('admin-auth.defaults.guard', 'admin');
        $data['created_by_admin_user_id'] = $this->user($adminUserGuard)->id;
        $data['updated_by_admin_user_id'] = $this->user($adminUserGuard)->id;

        //Add your code for manipulation with request data here

        return $data;
    }

    public function getPostIds(): Collection
    {
        $data = $this->getModifiedData();

        return $data['posts']->pluck('id');
    }
}
