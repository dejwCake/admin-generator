<?php

declare(strict_types=1);

namespace App\Exports;

use App\Feed\Post;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class PostsExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection(): Collection
    {
        return Post::all();
    }

    public function headings(): array
    {
        return [
            trans('admin.post.columns.id'),
            trans('admin.post.columns.title'),
        ];
    }

    /**
     * @param Post $post
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function map($post): array
    {
        return [
            $post->id,
            $post->title,
        ];
    }
}
