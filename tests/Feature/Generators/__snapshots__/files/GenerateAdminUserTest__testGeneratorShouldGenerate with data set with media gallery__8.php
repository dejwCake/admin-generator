@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.admin-user.actions.create'))

@section('body')

    <div class="container-xl">

        <admin-user-form
            :action="'{{ $action }}'"
            :activation="!!'{{ $activation }}'"
            :language-options="{{ $locales->toJson() }}"
            :role-options="{{ $roles->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.admin-user.actions.create'),
                'columns' => [
                    'first_name' => trans('admin.admin-user.columns.first_name'),
                    'last_name' => trans('admin.admin-user.columns.last_name'),
                    'email' => trans('admin.admin-user.columns.email'),
                    'password' => trans('admin.admin-user.columns.password'),
                    'password_repeat' => trans('admin.admin-user.columns.password_repeat'),
                    'activated' => trans('admin.admin-user.columns.activated'),
                    'forbidden' => trans('admin.admin-user.columns.forbidden'),
                    'language' => trans('admin.admin-user.columns.language'),
                ],
                'relations' => [
                    'roles' => trans('admin.admin-user.relations.roles'),
                ],
                'gallery' => trans('brackets/admin-ui::admin.forms.gallery'),
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            :media="{{ json_encode([
                'gallery' => [
                    'url' => $mediaUploadUrl,
                    'collection' => 'gallery',
                    'label' => trans('admin.admin-user.collections.gallery'),
                    'maxNumberOfFiles' => $galleryCollection->getMaxNumberOfFiles() ?: 5000,
                    'maxFileSizeInMb' => $galleryCollection->getMaxFileSize() ? round($galleryCollection->getMaxFileSize()/1024/1024, 2) : 2,
                    'acceptedFileTypes' => $galleryCollection->getAcceptedFileTypes() ? implode(',', $galleryCollection->getAcceptedFileTypes()) : null,
                    'isPrivate' => $galleryCollection->isPrivate(),
                    'uploadedMedia' => [],
                ],
            ]) }}"
            v-cloak
        ></admin-user-form>

    </div>

@endsection
