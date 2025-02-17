<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\AdminUser;

use App\Models\AdminUser;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property AdminUser $adminUser
 */
class DestroyAdminUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.admin-user.delete', $this->adminUser);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
