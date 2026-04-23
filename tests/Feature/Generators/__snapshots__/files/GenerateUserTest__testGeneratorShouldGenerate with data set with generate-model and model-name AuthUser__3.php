<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Auth\User;

use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
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
        return $gate->allows('admin.auth.user.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
                'string',
            ],
            'email_verified_at' => [
                'nullable',
                'date',
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

            'roles' => [
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
        $data['roles'] = new Collection($data['roles'] ?? []);

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
