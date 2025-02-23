<?php

declare(strict_types=1);

namespace App\Exports;

use App\Billing\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromCollection, WithMapping, WithHeadings
{
    /**
     * @return Collection
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function collection()
    {
        return Category::all();
    }

    public function headings(): array
    {
        return [
            trans('admin.category.columns.id'),
            trans('admin.category.columns.user_id'),
            trans('admin.category.columns.title'),
            trans('admin.category.columns.slug'),
            trans('admin.category.columns.perex'),
            trans('admin.category.columns.published_at'),
            trans('admin.category.columns.date_start'),
            trans('admin.category.columns.time_start'),
            trans('admin.category.columns.date_time_end'),
            trans('admin.category.columns.text'),
            trans('admin.category.columns.description'),
            trans('admin.category.columns.enabled'),
            trans('admin.category.columns.send'),
            trans('admin.category.columns.price'),
            trans('admin.category.columns.views'),
            trans('admin.category.columns.created_by_admin_user_id'),
            trans('admin.category.columns.updated_by_admin_user_id'),
        ];
    }

    /**
     * @param Category $category
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function map($category): array
    {
        return [
            $category->id,
            $category->user_id,
            $category->title,
            $category->slug,
            $category->perex,
            $category->published_at,
            $category->date_start,
            $category->time_start,
            $category->date_time_end,
            $category->text,
            $category->description,
            $category->enabled,
            $category->send,
            $category->price,
            $category->views,
            $category->created_by_admin_user_id,
            $category->updated_by_admin_user_id,
        ];
    }
}
