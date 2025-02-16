<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\AdminUser;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.admin-user.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(Config $config): array
    {
        $rules = [
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['required', 'email', Rule::unique('admin_users', 'email')->whereNull('deleted_at'), 'string'],
            'password' => ['required', 'confirmed', 'min:7', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/', 'string'],
            'forbidden' => ['required', 'boolean'],
            'language' => ['required', 'string'],

            'roles' => ['array'],
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
        if (isset($data['password'])) {
            $hasher = app(Hasher::class);
            assert($hasher instanceof Hasher);
            $data['password'] = $hasher->make($data['password']);
        }

        return $data;
    }
}
