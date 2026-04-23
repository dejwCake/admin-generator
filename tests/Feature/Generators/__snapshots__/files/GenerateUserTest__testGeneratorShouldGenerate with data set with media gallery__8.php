@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.edit', ['name' => $user->name]))

@section('body')

    <div class="container-xl">

        <user-form
            :action="'{{ $action }}'"
            :data="{{ $user->toJson() }}"
            :role-options="{{ $roles->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.user.actions.edit', ['name' => $user->name]),
                'columns' => [
                    'name' => trans('admin.user.columns.name'),
                    'email' => trans('admin.user.columns.email'),
                    'email_verified_at' => trans('admin.user.columns.email_verified_at'),
                    'password' => trans('admin.user.columns.password'),
                    'password_repeat' => trans('admin.user.columns.password_repeat'),
                ],
                'relations' => [
                    'roles' => trans('admin.user.relations.roles'),
                ],
                'gallery' => trans('brackets/admin-ui::admin.forms.gallery'),
                'select_date_and_time' => trans('brackets/admin-ui::admin.forms.select_date_and_time'),
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            :media="{{ json_encode([
                'gallery' => [
                    'url' => $mediaUploadUrl,
                    'collection' => 'gallery',
                    'label' => trans('admin.user.collections.gallery'),
                    'maxNumberOfFiles' => $galleryCollection->getMaxNumberOfFiles() ?: 5000,
                    'maxFileSizeInMb' => $galleryCollection->getMaxFileSize() ? round($galleryCollection->getMaxFileSize()/1024/1024, 2) : 2,
                    'acceptedFileTypes' => $galleryCollection->getAcceptedFileTypes() ? implode(',', $galleryCollection->getAcceptedFileTypes()) : null,
                    'isPrivate' => $galleryCollection->isPrivate(),
                    'uploadedMedia' => $galleryMedia && $galleryMedia->count() > 0 ? $galleryMedia->toArray() : [],
                ],
            ]) }}"
            v-cloak
        ></user-form>

    </div>

@endsection
