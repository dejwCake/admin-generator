<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class PostsExport implements FromQuery, WithMapping, WithHeadings, WithCustomChunkSize
{
    private const int CHUNK = 1000;

    /**
     * @return Builder<Post>
     */
    public function query(): Builder
    {
        return Post::query()->orderBy('id');
    }

    public function chunkSize(): int
    {
        return self::CHUNK;
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
