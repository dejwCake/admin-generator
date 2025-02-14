<?php

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
     */
    public function collection()
    {
        return Category::all();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            trans('admin.category.columns.id'),
            trans('admin.category.columns.title'),
        ];
    }

    /**
     * @param Category $category
     * @return array
     *
     */
    public function map($category): array
    {
        return [
            $category->id,
            $category->title,
        ];
    }
}
