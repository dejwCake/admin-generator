<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Cat;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;

final class ExportCat extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->allows('admin.cat.index');
    }
}
