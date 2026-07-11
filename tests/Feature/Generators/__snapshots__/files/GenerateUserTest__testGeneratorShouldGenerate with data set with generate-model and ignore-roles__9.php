@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.edit', ['name' => $user->name]))

@section('body')

    <div class="container-xl">

        <user-form
            :action="'{{ $action }}'"
            :data="{{ $user->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.user.actions.edit', ['name' => $user->name]),
                'columns' => [
                    'name' => trans('admin.user.columns.name'),
                    'email' => trans('admin.user.columns.email'),
                    'email_verified_at' => trans('admin.user.columns.email_verified_at'),
                    'password' => trans('admin.user.columns.password'),
                    'password_repeat' => trans('admin.user.columns.password_repeat'),
                ],
                'select_date_and_time' => trans('brackets/admin-ui::admin.forms.select_date_and_time'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></user-form>

    </div>

@endsection
