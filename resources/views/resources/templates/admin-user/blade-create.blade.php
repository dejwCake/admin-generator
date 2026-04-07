@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Str;
    assert($relations instanceof RelationCollection);
    assert($columns instanceof ColumnCollection);
    assert($publishedColumns instanceof ColumnCollection);
@endphp
{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.create'))

{{'@'}}section('body')

    <div class="container-xl">

        <{{ $modelJSName }}-form
            :action="'{{'{{'}} $action }}'"
            :activation="!!'{{'{{'}} $activation }}'"
            :language-options="{{'{{'}} $locales->toJson() }}"
@if($hasTranslatable)
            :locales="{{'{{'}} json_encode($locales) }}"
            :send-empty-locales="false"
@endif
@if($hasWysiwyg)
            :wysiwyg-upload-url="'{{'{{'}} $wysiwygUploadUrl }}'"
@endif
@foreach($relations->getBelongsToMany() as $belongsToMany)
            :{{ Str::singular(str_replace('_', '-', $belongsToMany->relatedTable)) }}-options="{{'{{'}} ${{ $belongsToMany->relatedTable }}->toJson() }}"
@endforeach
@foreach($relations->getBelongsTo() as $belongsTo)
@if(!$relations->hasRelatedTableInBelongsToMany($belongsTo->relatedTable))
            :{{ Str::singular(str_replace('_', '-', $belongsTo->relatedTable)) }}-options="{{'{{'}} ${{ $belongsTo->relatedTable }}->toJson() }}"
@endif
@endforeach
            :translations="{{'{{'}} json_encode([
                'form_title' => trans('admin.{{ $modelLangFormat }}.actions.create'),
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
@if($relations->hasBelongsToMany())
                'relations' => [
@foreach($relations->getBelongsToMany() as $belongsToMany)
                    '{{ Str::lcfirst(Str::plural($belongsToMany->relatedModelName)) }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ Str::lcfirst(Str::plural($belongsToMany->relatedModelName)) }}'),
@endforeach
                ],
@endif
@if($galleryCollections->isNotEmpty())
                'gallery' => trans('brackets/admin-ui::admin.forms.gallery'),
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
                    'uploadedMedia' => [],
                ],
@endforeach
            ]) }}"
@endif
            v-cloak
        ></{{ $modelJSName }}-form>

    </div>

{{'@'}}endsection
