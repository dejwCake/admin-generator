<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\AdminUser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class AdminUsersExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection(): Collection
    {
        return AdminUser::all();
    }

    public function headings(): array
    {
        return [
            trans('admin.admin-user.columns.id'),
            trans('admin.admin-user.columns.first_name'),
            trans('admin.admin-user.columns.last_name'),
            trans('admin.admin-user.columns.email'),
            trans('admin.admin-user.columns.activated'),
            trans('admin.admin-user.columns.forbidden'),
            trans('admin.admin-user.columns.language'),
        ];
    }

    /**
     * @param AdminUser $adminUser
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
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
