@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.show', ['name' => $user->name]))

@section('body')

    <div class="container-xl">

        <user-show
            :data="{{ $user->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.user.actions.show', ['name' => $user->name]),
                'columns' => [
                    'id' => trans('admin.user.columns.id'),
                    'name' => trans('admin.user.columns.name'),
                    'email' => trans('admin.user.columns.email'),
                    'email_verified_at' => trans('admin.user.columns.email_verified_at'),
                    'created_at' => trans('admin.user.columns.created_at'),
                    'updated_at' => trans('admin.user.columns.updated_at'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></user-show>

    </div>

@endsection
