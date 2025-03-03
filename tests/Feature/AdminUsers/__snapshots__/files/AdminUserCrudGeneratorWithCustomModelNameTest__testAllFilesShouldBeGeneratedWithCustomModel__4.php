<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property User $user
 */
class UpdateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.user.edit', $this->user);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(Config $config): array
    {
        $rules = [
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['sometimes', 'email', Rule::unique('admin_users', 'email')
                ->ignore($this->user->getKey(), $this->user->getKeyName())
                ->whereNull('deleted_at'), 'string'],
            'password' => ['sometimes', 'confirmed', 'min:7', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/', 'string'],
            'forbidden' => ['sometimes', 'boolean'],
            'language' => ['sometimes', 'string'],

            'roles' => ['sometimes', 'array'],
        ];

        if ($config->get('admin-auth.activation_enabled')) {
            $rules['activated'] = ['required', 'boolean'];
        }

        return $rules;
    }

    /**
     * Modify input data
     */
    public function getModifiedData(): array
    {
        $config = app(Config::class);
        assert($config instanceof Config);
        $data = $this->validated();
        if (!$config->get('admin-auth.activation_enabled')) {
            $data['activated'] = true;
        }
        if (array_key_exists('password', $data) && ($data['password'] === '' || $data['password'] === null)) {
            unset($data['password']);
        }
        if (isset($data['password'])) {
            $hasher = app(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }
}
