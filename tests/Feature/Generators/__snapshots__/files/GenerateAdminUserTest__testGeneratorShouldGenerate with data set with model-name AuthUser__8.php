@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.auth_user.actions.create'))

@section('body')

    <div class="container-xl">

        <auth-user-form
            :action="'{{ $action }}'"
            :activation="!!'{{ $activation }}'"
            :language-options="{{ $locales->toJson() }}"
            :role-options="{{ $roles->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.auth_user.actions.create'),
                'columns' => [
                    'first_name' => trans('admin.auth_user.columns.first_name'),
                    'last_name' => trans('admin.auth_user.columns.last_name'),
                    'email' => trans('admin.auth_user.columns.email'),
                    'password' => trans('admin.auth_user.columns.password'),
                    'password_repeat' => trans('admin.auth_user.columns.password_repeat'),
                    'activated' => trans('admin.auth_user.columns.activated'),
                    'forbidden' => trans('admin.auth_user.columns.forbidden'),
                    'language' => trans('admin.auth_user.columns.language'),
                ],
                'relations' => [
                    'roles' => trans('admin.auth_user.relations.roles'),
                ],
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></auth-user-form>

    </div>

@endsection
