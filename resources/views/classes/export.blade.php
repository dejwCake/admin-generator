@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    assert($columns instanceof ColumnCollection)
@endphp
@php echo "<?php";
@endphp


declare(strict_types=1);

namespace {{ $exportNamespace }};

use {{ $modelFullName }};
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class {{ $classBaseName }} implements FromQuery, WithMapping, WithHeadings, WithChunkReading
{
    private const int CHUNK = 1000;

    /**
     * {{'@'}}return Builder<{{ $modelBaseName }}>
     */
    public function query(): Builder
    {
        return {{ $modelBaseName }}::query()->orderBy('id');
    }

    public function chunkSize(): int
    {
        return self::CHUNK;
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
