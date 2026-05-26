@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    assert($columns instanceof ColumnCollection)
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $exportNamespace }};

use {{ $modelFullName }};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class {{ $classBaseName }} implements FromCollection, WithMapping, WithHeadings
{
    public function collection(): Collection
    {
        return {{ $modelBaseName }}::all();
    }

    public function headings(): array
    {
        return [
@foreach($columns as $column)
            trans('admin.{{ $modelLangFormat }}.columns.{{ $column->name }}'),
@endforeach
        ];
    }

    /**
     * {{'@'}}param {{ $modelBaseName }} ${{ $modelVariableName }}
     * {{'@'}}phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function map(${{ $modelVariableName }}): array
    {
        return [
@foreach($columns as $column)
@if($column->phpType === 'bool')
            ${{ $modelVariableName }}->{{ $column->name }} === null ? '' : (${{ $modelVariableName }}->{{ $column->name }} ? __('Yes') : __('No')),
@elseif($column->isArray())
            is_array(${{ $modelVariableName }}->{{ $column->name }}) ? implode(', ', ${{ $modelVariableName }}->{{ $column->name }}) : ${{ $modelVariableName }}->{{ $column->name }},
@else
            ${{ $modelVariableName }}->{{ $column->name }},
@endif
@endforeach
        ];
    }
}
