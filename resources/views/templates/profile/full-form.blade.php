@php
    use Brackets\AdminGenerator\Dtos\Columns\Column;
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    assert($profileColumns instanceof ColumnCollection);
@endphp
{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.edit_profile'))

{{'@'}}section('body')

    <div class="container-xl">

        <{{ $modelJSName }}-form
            :action="'{{'{{'}} $action }}'"
            :data="{{'{{'}} ${{ $modelVariableName }}->toJson() }}"
@if($profileColumns->hasByName('language'))
            :language-options="{{'{{'}} $locales->toJson() }}"
@endif
            :translations="{{'{{'}} json_encode([
                'form_title' => trans('admin.{{ $modelLangFormat }}.actions.edit_profile'),
                'columns' => [
@foreach($profileColumns as $column)
                    '{{ $column->name }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ $column->name }}'),
@endforeach
                ],
@if($profileColumns->hasByName('language'))
                'select_an_option' => trans('brackets/admin-ui::admin.forms.select_an_option'),
@endif
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            :media="{{'{{'}} json_encode([
                'url' => $mediaUploadUrl,
                'collection' => 'avatar',
                'maxNumberOfFiles' => $avatarCollection->getMaxNumberOfFiles() ?: 1,
                'maxFileSizeInMb' => $avatarCollection->getMaxFileSize() ? round($avatarCollection->getMaxFileSize()/1024/1024, 2) : 2,
                'acceptedFileTypes' => $avatarCollection->getAcceptedFileTypes() ? implode(',', $avatarCollection->getAcceptedFileTypes()) : null,
                'uploadedMedia' => $avatarMedia && $avatarMedia->count() > 0 ? $avatarMedia->toArray() : [],
            ]) }}"
        ></{{ $modelJSName }}-form>

    </div>

{{'@'}}endsection
