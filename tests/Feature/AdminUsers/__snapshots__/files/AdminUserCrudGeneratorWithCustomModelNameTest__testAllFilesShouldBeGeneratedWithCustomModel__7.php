<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\AdminUser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminUsersExport implements FromCollection, WithMapping, WithHeadings
{
    /**
     * @return Collection
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function collection()
    {
        return AdminUser::all();
    }

    public function headings(): array
    {
        return [
            __('admin.admin-user.columns.id'),
            __('admin.admin-user.columns.first_name'),
            __('admin.admin-user.columns.last_name'),
            __('admin.admin-user.columns.email'),
            __('admin.admin-user.columns.activated'),
            __('admin.admin-user.columns.forbidden'),
            __('admin.admin-user.columns.language'),
        ];
    }

    /**
     * @param AdminUser $adminUser
     */
    public function map($adminUser): array
    {
        return [
            $adminUser->id,
            $adminUser->first_name,
            $adminUser->last_name,
            $adminUser->email,
            $adminUser->activated,
            $adminUser->forbidden,
            $adminUser->language,
        ];
    }
}
