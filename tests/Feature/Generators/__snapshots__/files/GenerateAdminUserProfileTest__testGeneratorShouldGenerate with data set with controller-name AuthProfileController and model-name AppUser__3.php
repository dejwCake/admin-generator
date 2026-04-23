@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.edit_password'))

@section('body')

    <div class="container-xl">

        <profile-edit-password-form
            :action="'{{ $action }}'"
            :data="{{ $user->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.user.actions.edit_password'),
                'columns' => [
                    'password' => trans('admin.user.columns.password'),
                    'password_repeat' => trans('admin.user.columns.password_repeat'),
                ],
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
        ></profile-edit-password-form>

    </div>

@endsection
