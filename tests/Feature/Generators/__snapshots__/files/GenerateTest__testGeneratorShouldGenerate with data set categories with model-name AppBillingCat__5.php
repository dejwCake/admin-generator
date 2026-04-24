<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Cat;

use App\Billing\Cat;
use Brackets\Translatable\Http\Requests\TranslatableFormRequest;
use Carbon\CarbonImmutable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property Cat $cat
 */
final class UpdateCat extends TranslatableFormRequest
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
            'user_id' => [
                'nullable',
                'integer',
            ],
            'title' => [
                'sometimes',
                Rule::unique('categories', 'title')
                    ->ignore($this->cat->getKey(), $this->cat->getKeyName()),
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
                'sometimes',
                'string',
            ],
            'slug' => [
                'sometimes',
                Rule::unique('categories', 'slug')
                    ->ignore($this->cat->getKey(), $this->cat->getKeyName()),
                'string',
            ],
            'perex' => [
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
                'sometimes',
                'date',
            ],
            'enabled' => [
                'sometimes',
                'boolean',
            ],
            'send' => [
                'sometimes',
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
                'sometimes',
                'integer',
            ],

            'publish_now' => [
                'nullable',
                'boolean',
            ],
            'unpublish_now' => [
                'nullable',
                'boolean',
            ],

            'posts' => [
                'sometimes',
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
            'long_text' => [
                'nullable',
                'string',
            ],
            'text' => [
                'sometimes',
                'string',
            ],
            'description' => [
                'sometimes',
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
        if (isset($data['posts'])) {
            $data['posts'] = new Collection($data['posts'] ?? []);
        }

        if (array_key_exists('password', $data) && ($data['password'] === '' || $data['password'] === null)) {
            unset($data['password']);
        }
        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        if (isset($data['publish_now']) && $data['publish_now'] === true) {
            $data['published_at'] = CarbonImmutable::now();
        }

        if (isset($data['unpublish_now']) && $data['unpublish_now'] === true) {
            $data['published_at'] = null;
        }

        $config = Container::getInstance()->make(Config::class);
        assert($config instanceof Config);
        $adminUserGuard = $config->get('admin-auth.defaults.guard', 'admin');
        $data['updated_by_admin_user_id'] = $this->user($adminUserGuard)->id;

        //Add your code for manipulation with request data here

        return $data;
    }

    public function getPostIds(): ?Collection
    {
        $data = $this->getModifiedData();
        if (!isset($data['posts'])) {
            return null;
        }

        return $data['posts']->pluck('id');
    }
}
