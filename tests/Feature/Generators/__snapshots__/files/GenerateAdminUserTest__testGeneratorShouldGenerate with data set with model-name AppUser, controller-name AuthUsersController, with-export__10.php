@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.show', ['name' => $user->first_name]))

@section('body')

    <div class="container-xl">

        <user-show
            :data="{{ $user->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.user.actions.show', ['name' => $user->first_name]),
                'columns' => [
                    'id' => trans('admin.user.columns.id'),
                    'first_name' => trans('admin.user.columns.first_name'),
                    'last_name' => trans('admin.user.columns.last_name'),
                    'email' => trans('admin.user.columns.email'),
                    'activated' => trans('admin.user.columns.activated'),
                    'forbidden' => trans('admin.user.columns.forbidden'),
                    'language' => trans('admin.user.columns.language'),
                    'created_at' => trans('admin.user.columns.created_at'),
                    'updated_at' => trans('admin.user.columns.updated_at'),
                ],
                'relations' => [
                    'roles' => trans('admin.user.relations.roles'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></user-show>

    </div>

@endsection
