<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property User $user
 */
class ImpersonalLoginUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.user.impersonal-login', $this->user);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
