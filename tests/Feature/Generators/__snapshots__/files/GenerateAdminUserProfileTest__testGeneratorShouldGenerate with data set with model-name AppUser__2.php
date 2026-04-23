@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.edit_profile'))

@section('body')

    <div class="container-xl">

        <profile-edit-profile-form
            :action="'{{ $action }}'"
            :data="{{ $user->toJson() }}"
            :language-options="{{ $locales->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.user.actions.edit_profile'),
                'columns' => [
                    'first_name' => trans('admin.user.columns.first_name'),
                    'last_name' => trans('admin.user.columns.last_name'),
                    'email' => trans('admin.user.columns.email'),
                    'language' => trans('admin.user.columns.language'),
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
        ></profile-edit-profile-form>

    </div>

@endsection
