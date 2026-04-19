@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.edit', ['name' => $user->first_name]))

@section('body')

    <div class="container-xl">

        <user-form
            :action="'{{ $action }}'"
            :data="{{ $user->toJson() }}"
            :activation="!!'{{ $activation }}'"
            :language-options="{{ $locales->toJson() }}"
            :role-options="{{ $roles->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.user.actions.edit', ['name' => $user->first_name . ' ' . $user->last_name]),
                'columns' => [
                    'first_name' => trans('admin.user.columns.first_name'),
                    'last_name' => trans('admin.user.columns.last_name'),
                    'email' => trans('admin.user.columns.email'),
                    'password' => trans('admin.user.columns.password'),
                    'password_repeat' => trans('admin.user.columns.password_repeat'),
                    'activated' => trans('admin.user.columns.activated'),
                    'forbidden' => trans('admin.user.columns.forbidden'),
                    'language' => trans('admin.user.columns.language'),
                ],
                'relations' => [
                    'roles' => trans('admin.user.relations.roles'),
                ],
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></user-form>

    </div>

@endsection
