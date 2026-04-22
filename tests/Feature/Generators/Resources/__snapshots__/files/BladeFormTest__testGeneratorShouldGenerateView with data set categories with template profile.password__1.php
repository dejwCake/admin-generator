@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.category.actions.edit_password'))

@section('body')

    <div class="container-xl">

        <category-form
            :action="'{{ $action }}'"
            :data="{{ $category->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.category.actions.edit_password'),
                'columns' => [
                    'password' => trans('admin.category.columns.password'),
                    'password_repeat' => trans('admin.category.columns.password_repeat'),
                ],
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
        ></category-form>

    </div>

@endsection
