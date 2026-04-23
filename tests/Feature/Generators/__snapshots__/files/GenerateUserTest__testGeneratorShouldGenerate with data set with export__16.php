<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithMapping, WithHeadings
{
    /**
     * @return Collection
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function collection()
    {
        return User::all();
    }

    public function headings(): array
    {
        return [
            trans('admin.user.columns.id'),
            trans('admin.user.columns.name'),
            trans('admin.user.columns.email'),
            trans('admin.user.columns.email_verified_at'),
        ];
    }

    /**
     * @param User $user
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->email_verified_at,
        ];
    }
}
