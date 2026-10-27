@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_cat.actions.show', ['name' => $cat->title]))

@section('body')

    <div class="container-xl">

        <billing-cat-show
            :data="{{ $cat->toJson() }}"
            :back-url="'{{ $backUrl }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.billing_cat.actions.show', ['name' => $cat->title]),
                'columns' => [
                    'id' => trans('admin.billing_cat.columns.id'),
                    'uuid' => trans('admin.billing_cat.columns.uuid'),
                    'user_id' => trans('admin.billing_cat.columns.user_id'),
                    'title' => trans('admin.billing_cat.columns.title'),
                    'name' => trans('admin.billing_cat.columns.name'),
                    'first_name' => trans('admin.billing_cat.columns.first_name'),
                    'last_name' => trans('admin.billing_cat.columns.last_name'),
                    'subject' => trans('admin.billing_cat.columns.subject'),
                    'email' => trans('admin.billing_cat.columns.email'),
                    'language' => trans('admin.billing_cat.columns.language'),
                    'slug' => trans('admin.billing_cat.columns.slug'),
                    'perex' => trans('admin.billing_cat.columns.perex'),
                    'long_text' => trans('admin.billing_cat.columns.long_text'),
                    'published_at' => trans('admin.billing_cat.columns.published_at'),
                    'published_to' => trans('admin.billing_cat.columns.published_to'),
                    'date_start' => trans('admin.billing_cat.columns.date_start'),
                    'time_start' => trans('admin.billing_cat.columns.time_start'),
                    'date_time_end' => trans('admin.billing_cat.columns.date_time_end'),
                    'released_at' => trans('admin.billing_cat.columns.released_at'),
                    'text' => trans('admin.billing_cat.columns.text'),
                    'description' => trans('admin.billing_cat.columns.description'),
                    'enabled' => trans('admin.billing_cat.columns.enabled'),
                    'send' => trans('admin.billing_cat.columns.send'),
                    'price' => trans('admin.billing_cat.columns.price'),
                    'rating' => trans('admin.billing_cat.columns.rating'),
                    'views' => trans('admin.billing_cat.columns.views'),
                    'created_by_admin_user_id' => trans('admin.billing_cat.columns.created_by_admin_user_id'),
                    'updated_by_admin_user_id' => trans('admin.billing_cat.columns.updated_by_admin_user_id'),
                    'created_at' => trans('admin.billing_cat.columns.created_at'),
                    'updated_at' => trans('admin.billing_cat.columns.updated_at'),
                ],
                'relations' => [
                    'posts' => trans('admin.billing_cat.relations.posts'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
                'created_on' => trans('brackets/admin-ui::admin.forms.created_on'),
                'updated_on' => trans('brackets/admin-ui::admin.forms.updated_on'),
            ]) }}"
            v-cloak
        ></billing-cat-show>

    </div>

@endsection
