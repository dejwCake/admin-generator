@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    assert($columns instanceof ColumnCollection);
@endphp
{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.show', ['name' => ${{ $modelVariableName }}->{{ $modelLabelColumn }}]))

{{'@'}}section('body')

    <div class="container-xl">

        <{{ $modelJSName }}-show
            :data="{{'{{'}} ${{ $modelVariableName }}->toJson() }}"
            :back-url="'{{'{{'}} route('admin/{{ $resource }}/index') }}'"
            :translations="{{'{{'}} json_encode([
                'show_title' => trans('admin.{{ $modelLangFormat }}.actions.show', ['name' => ${{ $modelVariableName }}->{{ $modelLabelColumn }}]),
                'columns' => [
@foreach($columns as $column)
                    '{{ $column->name }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ $column->name }}'),
@endforeach
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></{{ $modelJSName }}-show>

    </div>

{{'@'}}endsection
