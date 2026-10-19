@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.admin-user.actions.show', ['name' => $adminUser->first_name]))

@section('body')

    <div class="container-xl">

        <admin-user-show
            :data="{{ $adminUser->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.admin-user.actions.show', ['name' => $adminUser->first_name]),
                'columns' => [
                    'id' => trans('admin.admin-user.columns.id'),
                    'first_name' => trans('admin.admin-user.columns.first_name'),
                    'last_name' => trans('admin.admin-user.columns.last_name'),
                    'email' => trans('admin.admin-user.columns.email'),
                    'activated' => trans('admin.admin-user.columns.activated'),
                    'forbidden' => trans('admin.admin-user.columns.forbidden'),
                    'language' => trans('admin.admin-user.columns.language'),
                    'created_at' => trans('admin.admin-user.columns.created_at'),
                    'updated_at' => trans('admin.admin-user.columns.updated_at'),
                ],
                'relations' => [
                    'roles' => trans('admin.admin-user.relations.roles'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></admin-user-show>

    </div>

@endsection
