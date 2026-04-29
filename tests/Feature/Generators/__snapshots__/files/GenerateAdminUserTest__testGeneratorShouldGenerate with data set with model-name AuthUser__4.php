<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Auth\User;

use App\Models\Auth\User;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property User $user
 */
final class UpdateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.auth.user.edit', $this->user);
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
                'sometimes',
                'email',
                Rule::unique('admin_users', 'email')
                    ->ignore($this->user->getKey(), $this->user->getKeyName())
                    ->whereNull('deleted_at'),
                'string',
            ],
            'password' => [
                'sometimes',
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
                'sometimes',
                'boolean',
            ],
            'language' => [
                'sometimes',
                'string',
            ],

            'roles' => [
                'sometimes',
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
        if (isset($data['roles'])) {
            $data['roles'] = new Collection($data['roles'] ?? []);
        }

        $config = Container::getInstance()->make(Config::class);
        assert($config instanceof Config);
        if (!$config->get('admin-auth.activation_enabled')) {
            $data['activated'] = true;
        }
        if (array_key_exists('password', $data) && ($data['password'] === '' || $data['password'] === null)) {
            unset($data['password']);
        }
        if (isset($data['password'])) {
            $hasher = Container::getInstance()->make(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }

    public function getRoleIds(): ?Collection
    {
        $data = $this->getModifiedData();
        if (!isset($data['roles'])) {
            return null;
        }

        return $data['roles']->pluck('id');
    }
}
