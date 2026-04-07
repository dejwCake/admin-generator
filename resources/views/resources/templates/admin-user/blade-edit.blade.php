@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    assert($relations instanceof RelationCollection);
    assert($columns instanceof ColumnCollection);
    assert($publishedColumns instanceof ColumnCollection);
@endphp
{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.edit', ['name' => ${{ $modelVariableName }}->{{ $modelLabelColumn }}]))

{{'@'}}section('body')

    <div class="container-xl">

        <{{ $modelJSName }}-form
            :action="'{{'{{'}} $action }}'"
@if($hasTranslatable)
            :data="{{'{{'}} ${{ $modelVariableName }}->toJsonAllLocales() }}"
            :locales="{{'{{'}} json_encode($locales) }}"
            :send-empty-locales="false"
@else
            :data="{{'{{'}} ${{ $modelVariableName }}->toJson() }}"
@endif
            :activation="!!'@{{ $activation }}'"
            :language-options="{{'{{'}} $locales->toJson() }}"
@if($hasWysiwyg)
            :wysiwyg-upload-url="'{{'{{'}} $wysiwygUploadUrl }}'"
@endif
@if($hasCreatedByAdminUser || $hasUpdatedByAdminUser)
            :show-history="true"
@endif
@foreach($relations->getBelongsToMany() as $belongsToMany)
            :{{ $belongsToMany->optionsAttributeName }}="{{'{{'}} ${{ $belongsToMany->relatedTable }}->toJson() }}"
@endforeach
@foreach($relations->getBelongsTo() as $belongsTo)
@if(!$relations->hasRelatedTableInBelongsToMany($belongsTo->relatedTable))
            :{{ $belongsTo->optionsAttributeName }}="{{'{{'}} ${{ $belongsTo->relatedTable }}->toJson() }}"
@endif
@endforeach
            :translations="{{'{{'}} json_encode([
                'form_title' => trans('admin.{{ $modelLangFormat }}.actions.edit', ['name' => ${{ $modelVariableName }}->first_name . ' ' . ${{ $modelVariableName }}->last_name]),
                'columns' => [
@foreach($columns as $column)
@if(!in_array($column->name, ['created_by_admin_user_id', 'updated_by_admin_user_id'], true))
                    '{{ $column->name }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ $column->name }}'),
@if($column->name === 'password')
                    '{{ $column->name }}_repeat' => trans('admin.{{ $modelLangFormat }}.columns.{{ $column->name }}_repeat'),
@endif
@endif
@endforeach
                ],
@if(count($relations->getBelongsToMany()) > 0)
                'relations' => [
@foreach($relations->getBelongsToMany() as $belongsToMany)
                    '{{ $belongsToMany->relationTranslationKey }}' => trans('admin.{{ $modelLangFormat }}.relations.{{ $belongsToMany->relationTranslationKey }}'),
@endforeach
                ],
@endif
@if($galleryCollections->isNotEmpty())
                'gallery' => trans('brackets/admin-ui::admin.forms.gallery'),
@endif
@if($hasCreatedByAdminUser || $hasUpdatedByAdminUser)
                'history' => trans('brackets/admin-ui::admin.forms.history'),
@endif
@if($hasTranslatable)
                'currently_editing_translation' => trans('brackets/admin-ui::admin.forms.currently_editing_translation'),
                'more_can_be_managed' => trans('brackets/admin-ui::admin.forms.more_can_be_managed'),
                'manage_translations' => trans('brackets/admin-ui::admin.forms.manage_translations'),
                'choose_translation_to_edit' => trans('brackets/admin-ui::admin.forms.choose_translation_to_edit'),
                'hide' => trans('brackets/admin-ui::admin.forms.hide'),
@endif
@if($hasDateColumns)
                'select_a_date' => trans('brackets/admin-ui::admin.forms.select_a_date'),
@endif
@if($hasTimeColumns)
                'select_a_time' => trans('brackets/admin-ui::admin.forms.select_a_time'),
@endif
@if($hasDatetimeColumns || $publishedColumns->isNotEmpty())
                'select_date_and_time' => trans('brackets/admin-ui::admin.forms.select_date_and_time'),
@endif
@if($relations->hasBelongsToMany())
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
@endif
@if($relations->hasBelongsTo())
                'select_an_option' => trans('brackets/admin-ui::admin.forms.select_an_option'),
@endif
@if($hasCreatedByAdminUser)
                'created_by' => trans('brackets/admin-ui::admin.forms.created_by'),
                'created_on' => trans('brackets/admin-ui::admin.forms.created_on'),
@endif
@if($hasUpdatedByAdminUser)
                'updated_by' => trans('brackets/admin-ui::admin.forms.updated_by'),
                'updated_on' => trans('brackets/admin-ui::admin.forms.updated_on'),
@endif
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
@if($mediaCollections->isNotEmpty())
            :media="{{'{{'}} json_encode([
@foreach($mediaCollections as $collection)
                '{{ $collection->collectionName }}' => [
                    'url' => $mediaUploadUrl,
                    'collection' => '{{ $collection->collectionName }}',
                    'label' => trans('admin.{{ $modelLangFormat }}.collections.{{ $collection->collectionName }}'),
                    'maxNumberOfFiles' => ${{ $collection->collectionName }}Collection->getMaxNumberOfFiles() ?: {{ $collection->maxFiles }},
                    'maxFileSizeInMb' => ${{ $collection->collectionName }}Collection->getMaxFileSize() ? round(${{ $collection->collectionName }}Collection->getMaxFileSize()/1024/1024, 2) : 2,
                    'acceptedFileTypes' => ${{ $collection->collectionName }}Collection->getAcceptedFileTypes() ? implode(',', ${{ $collection->collectionName }}Collection->getAcceptedFileTypes()) : null,
                    'isPrivate' => ${{ $collection->collectionName }}Collection->isPrivate(),
                    'uploadedMedia' => ${{ $collection->collectionName }}Media && ${{ $collection->collectionName }}Media->count() > 0 ? ${{ $collection->collectionName }}Media->toArray() : [],
                ],
@endforeach
            ]) }}"
@endif
            v-cloak
        ></{{ $modelJSName }}-form>

    </div>

{{'@'}}endsection
