<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Auth\User;

use App\Models\Auth\User;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
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
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
            ],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($this->user->getKey(), $this->user->getKeyName()),
                'string',
            ],
            'email_verified_at' => [
                'nullable',
                'date',
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

            'roles' => [
                'sometimes',
                'array',
            ],
            'roles.*.id' => [
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
        if (isset($data['roles'])) {
            $data['roles'] = new Collection($data['roles'] ?? []);
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
