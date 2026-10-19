@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.category.actions.show', ['name' => $category->title]))

@section('body')

    <div class="container-xl">

        <category-show
            :data="{{ $category->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.category.actions.show', ['name' => $category->title]),
                'columns' => [
                    'id' => trans('admin.category.columns.id'),
                    'user_id' => trans('admin.category.columns.user_id'),
                    'title' => trans('admin.category.columns.title'),
                    'name' => trans('admin.category.columns.name'),
                    'first_name' => trans('admin.category.columns.first_name'),
                    'last_name' => trans('admin.category.columns.last_name'),
                    'subject' => trans('admin.category.columns.subject'),
                    'email' => trans('admin.category.columns.email'),
                    'language' => trans('admin.category.columns.language'),
                    'slug' => trans('admin.category.columns.slug'),
                    'perex' => trans('admin.category.columns.perex'),
                    'long_text' => trans('admin.category.columns.long_text'),
                    'published_at' => trans('admin.category.columns.published_at'),
                    'published_to' => trans('admin.category.columns.published_to'),
                    'date_start' => trans('admin.category.columns.date_start'),
                    'time_start' => trans('admin.category.columns.time_start'),
                    'date_time_end' => trans('admin.category.columns.date_time_end'),
                    'released_at' => trans('admin.category.columns.released_at'),
                    'text' => trans('admin.category.columns.text'),
                    'description' => trans('admin.category.columns.description'),
                    'enabled' => trans('admin.category.columns.enabled'),
                    'send' => trans('admin.category.columns.send'),
                    'price' => trans('admin.category.columns.price'),
                    'rating' => trans('admin.category.columns.rating'),
                    'views' => trans('admin.category.columns.views'),
                    'created_by_admin_user_id' => trans('admin.category.columns.created_by_admin_user_id'),
                    'updated_by_admin_user_id' => trans('admin.category.columns.updated_by_admin_user_id'),
                    'created_at' => trans('admin.category.columns.created_at'),
                    'updated_at' => trans('admin.category.columns.updated_at'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></category-show>

    </div>

@endsection
