@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.create'))

@section('body')

    <div class="container-xl">

        <user-form
            :action="'{{ $action }}'"
            :role-options="{{ $roles->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.user.actions.create'),
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
                'select_date_and_time' => trans('brackets/admin-ui::admin.forms.select_date_and_time'),
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></user-form>

    </div>

@endsection
