@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.auth_user.actions.show', ['name' => $user->name]))

@section('body')

    <div class="container-xl">

        <auth-user-show
            :data="{{ $user->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.auth_user.actions.show', ['name' => $user->name]),
                'columns' => [
                    'id' => trans('admin.auth_user.columns.id'),
                    'name' => trans('admin.auth_user.columns.name'),
                    'email' => trans('admin.auth_user.columns.email'),
                    'email_verified_at' => trans('admin.auth_user.columns.email_verified_at'),
                    'created_at' => trans('admin.auth_user.columns.created_at'),
                    'updated_at' => trans('admin.auth_user.columns.updated_at'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></auth-user-show>

    </div>

@endsection
