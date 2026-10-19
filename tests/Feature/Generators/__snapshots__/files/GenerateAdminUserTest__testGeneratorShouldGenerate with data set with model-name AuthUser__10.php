@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.auth_user.actions.show', ['name' => $user->first_name]))

@section('body')

    <div class="container-xl">

        <auth-user-show
            :data="{{ $user->toJson() }}"
            :back-url="'{{ route('admin/auth-users/index') }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.auth_user.actions.show', ['name' => $user->first_name]),
                'columns' => [
                    'id' => trans('admin.auth_user.columns.id'),
                    'first_name' => trans('admin.auth_user.columns.first_name'),
                    'last_name' => trans('admin.auth_user.columns.last_name'),
                    'email' => trans('admin.auth_user.columns.email'),
                    'activated' => trans('admin.auth_user.columns.activated'),
                    'forbidden' => trans('admin.auth_user.columns.forbidden'),
                    'language' => trans('admin.auth_user.columns.language'),
                    'created_at' => trans('admin.auth_user.columns.created_at'),
                    'updated_at' => trans('admin.auth_user.columns.updated_at'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></auth-user-show>

    </div>

@endsection
