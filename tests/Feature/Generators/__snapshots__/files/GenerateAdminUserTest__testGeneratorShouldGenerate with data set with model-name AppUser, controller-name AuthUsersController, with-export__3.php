<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.user.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(Config $config): array
    {
        $rules = [
            'first_name' => [
                'nullable',
                'string',
            ],
            'last_name' => [
                'nullable',
                'string',
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('admin_users', 'email')
                    ->whereNull('deleted_at'),
                'string',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'string',
            ],
            'forbidden' => [
                'required',
                'boolean',
            ],
            'language' => [
                'required',
                'string',
            ],

            'roles' => [
                'array',
            ],
            'roles.*.id' => [
                'required',
                'integer',
            ],
        ];

        if ($config->get('admin-auth.activation_enabled')) {
            $rules['activated'] = [
                'required',
                'boolean',
            ];
        }

        return $rules;
    }

    /**
     * Modify input data
     */
    public function getModifiedData(): array
    {
        $data = $this->validated();
        $data['roles'] = new Collection($data['roles'] ?? []);

        $config = Container::getInstance()->make(Config::class);
        assert($config instanceof Config);
        if (!$config->get('admin-auth.activation_enabled')) {
            $data['activated'] = true;
        }
        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }

    public function getRoleIds(): Collection
    {
        $data = $this->getModifiedData();

        return $data['roles']->pluck('id');
    }
}
