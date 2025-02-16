<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Category;
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
            trans('admin.category.columns.title'),
        ];
    }

    /**
     * @param Category $category
     */
    public function map($category): array
    {
        return [
            $category->id,
            $category->title,
        ];
    }
}
