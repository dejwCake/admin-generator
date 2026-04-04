@php use Illuminate\Support\Str;
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
@if($hasWysiwyg)
            :wysiwyg-upload-url="'{{'{{'}} $wysiwygUploadUrl }}'"
@endif
@if($hasCreatedByAdminUser || $hasUpdatedByAdminUser)
            :show-history="true"
@endif
@foreach($relations['belongsToMany'] as $belongsToMany)
            :{{ Str::singular(str_replace('_', '-', $belongsToMany['related_table'])) }}-options="{{'{{'}} ${{ $belongsToMany['related_table'] }}->toJson() }}"
@endforeach
@foreach($foreignKeys as $foreignKey)
@if(!$belongsToManyTables->contains($foreignKey['relatedTable']))
            :{{ Str::singular(str_replace('_', '-', $foreignKey['relatedTable'])) }}-options="{{'{{'}} ${{ $foreignKey['relatedTable'] }}->toJson() }}"
@endif
@endforeach
            :translations="{{'{{'}} json_encode([
                'form_title' => trans('admin.{{ $modelLangFormat }}.actions.edit', ['name' => ${{ $modelVariableName }}->{{ $modelLabelColumn }}]),
                'columns' => [
@foreach($columns as $col)
@if(!in_array($col['name'], ['created_by_admin_user_id', 'updated_by_admin_user_id'], true))
                    '{{ $col['name'] }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}'),
@if($col['name'] === 'password')
                    '{{ $col['name'] }}_repeat' => trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}_repeat'),
@endif
@endif
@endforeach
                ],
@if(count($relations['belongsToMany']) > 0)
                'relations' => [
@foreach($relations['belongsToMany'] as $belongsToMany)
                    '{{ Str::lcfirst($belongsToMany['related_model_name_plural']) }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ Str::lcfirst($belongsToMany['related_model_name_plural']) }}'),
@endforeach
                ],
@endif
@if($rightMediaCollections->isNotEmpty())
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
@if($hasDatetimeColumns || $rightFormColumns->isNotEmpty())
                'select_date_and_time' => trans('brackets/admin-ui::admin.forms.select_date_and_time'),
@endif
@if($hasForeignKeys || count($relations['belongsToMany']) > 0)
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
@endif
@if($hasForeignKeys)
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
