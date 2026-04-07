@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.admin-user.actions.edit', ['name' => $adminUser->first_name]))

@section('body')

    <div class="container-xl">

        <admin-user-form
            :action="'{{ $action }}'"
            :data="{{ $adminUser->toJson() }}"
            :activation="!!'{{ $activation }}'"
            :language-options="{{ $locales->toJson() }}"
            :role-options="{{ $roles->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.admin-user.actions.edit', ['name' => $adminUser->first_name . ' ' . $adminUser->last_name]),
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
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></admin-user-form>

    </div>

@endsection
