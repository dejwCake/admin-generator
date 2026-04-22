@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.post.actions.edit', ['name' => $post->title]))

@section('body')

    <div class="container-xl">

        <post-form
            :action="'{{ $action }}'"
            :data="{{ $post->toJson() }}"
            :category-options="{{ $categories->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.post.actions.edit', ['name' => $post->title]),
                'columns' => [
                    'title' => trans('admin.post.columns.title'),
                ],
                'relations' => [
                    'categories' => trans('admin.post.relations.categories'),
                ],
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></post-form>

    </div>

@endsection
