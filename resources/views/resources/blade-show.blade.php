@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    assert($columns instanceof ColumnCollection);
    assert($relations instanceof RelationCollection);
@endphp
{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.show', ['name' => ${{ $modelVariableName }}->{{ $modelLabelColumn }}]))

{{'@'}}section('body')

    <div class="container-xl">

        <{{ $modelJSName }}-show
            :data="{{'{{'}} ${{ $modelVariableName }}->toJson() }}"
            :back-url="'{{'{{'}} $backUrl }}'"
            :translations="{{'{{'}} json_encode([
                'show_title' => trans('admin.{{ $modelLangFormat }}.actions.show', ['name' => ${{ $modelVariableName }}->{{ $modelLabelColumn }}]),
                'columns' => [
@foreach($columns as $column)
                    '{{ $column->name }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ $column->name }}'),
@endforeach
                ],
@if($relations->hasBelongsToMany())
                'relations' => [
@foreach($relations->getBelongsToMany() as $belongsToMany)
                    '{{ $belongsToMany->relationTranslationKey }}' => trans('admin.{{ $modelLangFormat }}.relations.{{ $belongsToMany->relationTranslationKey }}'),
@endforeach
                ],
@endif
                'back' => trans('brackets/admin-ui::admin.btn.back'),
@if($hasCreatedByAdminUser)
                'created_on' => trans('brackets/admin-ui::admin.forms.created_on'),
@endif
@if($hasUpdatedByAdminUser)
                'updated_on' => trans('brackets/admin-ui::admin.forms.updated_on'),
@endif
            ]) }}"
            v-cloak
        ></{{ $modelJSName }}-show>

    </div>

{{'@'}}endsection
