<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class UsersExport implements FromQuery, WithMapping, WithHeadings, WithChunkReading
{
    private const int CHUNK = 1000;

    /**
     * @return Builder<User>
     */
    public function query(): Builder
    {
        return User::query()->orderBy('id');
    }

    public function chunkSize(): int
    {
        return self::CHUNK;
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
