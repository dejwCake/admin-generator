@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.cat.actions.show', ['name' => $cat->title]))

@section('body')

    <div class="container-xl">

        <cat-show
            :data="{{ $cat->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.cat.actions.show', ['name' => $cat->title]),
                'columns' => [
                    'id' => trans('admin.cat.columns.id'),
                    'user_id' => trans('admin.cat.columns.user_id'),
                    'title' => trans('admin.cat.columns.title'),
                    'name' => trans('admin.cat.columns.name'),
                    'first_name' => trans('admin.cat.columns.first_name'),
                    'last_name' => trans('admin.cat.columns.last_name'),
                    'subject' => trans('admin.cat.columns.subject'),
                    'email' => trans('admin.cat.columns.email'),
                    'language' => trans('admin.cat.columns.language'),
                    'slug' => trans('admin.cat.columns.slug'),
                    'perex' => trans('admin.cat.columns.perex'),
                    'long_text' => trans('admin.cat.columns.long_text'),
                    'published_at' => trans('admin.cat.columns.published_at'),
                    'published_to' => trans('admin.cat.columns.published_to'),
                    'date_start' => trans('admin.cat.columns.date_start'),
                    'time_start' => trans('admin.cat.columns.time_start'),
                    'date_time_end' => trans('admin.cat.columns.date_time_end'),
                    'released_at' => trans('admin.cat.columns.released_at'),
                    'text' => trans('admin.cat.columns.text'),
                    'description' => trans('admin.cat.columns.description'),
                    'enabled' => trans('admin.cat.columns.enabled'),
                    'send' => trans('admin.cat.columns.send'),
                    'price' => trans('admin.cat.columns.price'),
                    'rating' => trans('admin.cat.columns.rating'),
                    'views' => trans('admin.cat.columns.views'),
                    'created_by_admin_user_id' => trans('admin.cat.columns.created_by_admin_user_id'),
                    'updated_by_admin_user_id' => trans('admin.cat.columns.updated_by_admin_user_id'),
                    'created_at' => trans('admin.cat.columns.created_at'),
                    'updated_at' => trans('admin.cat.columns.updated_at'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></cat-show>

    </div>

@endsection
