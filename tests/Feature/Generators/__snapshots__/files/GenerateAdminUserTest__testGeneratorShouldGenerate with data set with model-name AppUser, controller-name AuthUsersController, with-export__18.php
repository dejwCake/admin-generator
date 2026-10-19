<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class AdminUsersExport implements FromQuery, WithMapping, WithHeadings, WithChunkReading
{
    private const int CHUNK = 1000;

    /**
     * @return Builder<AdminUser>
     */
    public function query(): Builder
    {
        return AdminUser::query()->orderBy('id');
    }

    public function chunkSize(): int
    {
        return self::CHUNK;
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
            $adminUser->activated === null ? '' : ($adminUser->activated ? __('Yes') : __('No')),
            $adminUser->forbidden === null ? '' : ($adminUser->forbidden ? __('Yes') : __('No')),
            $adminUser->language,
        ];
    }
}
