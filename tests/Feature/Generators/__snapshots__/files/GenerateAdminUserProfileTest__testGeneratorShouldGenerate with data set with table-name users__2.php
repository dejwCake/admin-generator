@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.edit_profile'))

@section('body')

    <div class="container-xl">

        <profile-edit-profile-form
            :action="'{{ $action }}'"
            :data="{{ $user->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.user.actions.edit_profile'),
                'columns' => [
                    'name' => trans('admin.user.columns.name'),
                    'email' => trans('admin.user.columns.email'),
                    'email_verified_at' => trans('admin.user.columns.email_verified_at'),
                ],
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
