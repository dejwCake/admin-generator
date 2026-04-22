@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.category.actions.edit_profile'))

@section('body')

    <div class="container-xl">

        <category-form
            :action="'{{ $action }}'"
            :data="{{ $category->toJson() }}"
            :language-options="{{ $locales->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.category.actions.edit_profile'),
                'columns' => [
                    'user_id' => trans('admin.category.columns.user_id'),
                    'title' => trans('admin.category.columns.title'),
                    'name' => trans('admin.category.columns.name'),
                    'first_name' => trans('admin.category.columns.first_name'),
                    'last_name' => trans('admin.category.columns.last_name'),
                    'subject' => trans('admin.category.columns.subject'),
                    'email' => trans('admin.category.columns.email'),
                    'language' => trans('admin.category.columns.language'),
                    'slug' => trans('admin.category.columns.slug'),
                    'perex' => trans('admin.category.columns.perex'),
                    'long_text' => trans('admin.category.columns.long_text'),
                    'date_start' => trans('admin.category.columns.date_start'),
                    'time_start' => trans('admin.category.columns.time_start'),
                    'date_time_end' => trans('admin.category.columns.date_time_end'),
                    'released_at' => trans('admin.category.columns.released_at'),
                    'text' => trans('admin.category.columns.text'),
                    'description' => trans('admin.category.columns.description'),
                    'enabled' => trans('admin.category.columns.enabled'),
                    'send' => trans('admin.category.columns.send'),
                    'price' => trans('admin.category.columns.price'),
                    'rating' => trans('admin.category.columns.rating'),
                    'views' => trans('admin.category.columns.views'),
                ],
                'select_an_option' => trans('brackets/admin-ui::admin.forms.select_an_option'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            :media="{{ json_encode([
                'url' => $mediaUploadUrl,
                'collection' => 'avatar',
                'maxNumberOfFiles' => $avatarCollection->getMaxNumberOfFiles() ?: 1,
                'maxFileSizeInMb' => $avatarCollection->getMaxFileSize() ? round($avatarCollection->getMaxFileSize()/1024/1024, 2) : 2,
                'acceptedFileTypes' => $avatarCollection->getAcceptedFileTypes() ? implode(',', $avatarCollection->getAcceptedFileTypes()) : null,
                'uploadedMedia' => $avatarMedia && $avatarMedia->count() > 0 ? $avatarMedia->toArray() : [],
            ]) }}"
        ></category-form>

    </div>

@endsection
