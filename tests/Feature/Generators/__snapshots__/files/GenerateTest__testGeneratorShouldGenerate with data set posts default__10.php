@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.post.actions.show', ['name' => $post->title]))

@section('body')

    <div class="container-xl">

        <post-show
            :data="{{ $post->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.post.actions.show', ['name' => $post->title]),
                'columns' => [
                    'id' => trans('admin.post.columns.id'),
                    'title' => trans('admin.post.columns.title'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></post-show>

    </div>

@endsection
