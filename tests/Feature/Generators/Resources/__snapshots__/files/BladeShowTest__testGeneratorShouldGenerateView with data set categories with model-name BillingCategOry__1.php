@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_categ-ory.actions.show', ['name' => $categOry->title]))

@section('body')

    <div class="container-xl">

        <billing-categ-ory-show
            :data="{{ $categOry->toJson() }}"
            :back-url="'{{ route('admin/billing-categ-ories/index') }}'"
            :translations="{{ json_encode([
                'show_title' => trans('admin.billing_categ-ory.actions.show', ['name' => $categOry->title]),
                'columns' => [
                    'id' => trans('admin.billing_categ-ory.columns.id'),
                    'user_id' => trans('admin.billing_categ-ory.columns.user_id'),
                    'title' => trans('admin.billing_categ-ory.columns.title'),
                    'name' => trans('admin.billing_categ-ory.columns.name'),
                    'first_name' => trans('admin.billing_categ-ory.columns.first_name'),
                    'last_name' => trans('admin.billing_categ-ory.columns.last_name'),
                    'subject' => trans('admin.billing_categ-ory.columns.subject'),
                    'email' => trans('admin.billing_categ-ory.columns.email'),
                    'language' => trans('admin.billing_categ-ory.columns.language'),
                    'slug' => trans('admin.billing_categ-ory.columns.slug'),
                    'perex' => trans('admin.billing_categ-ory.columns.perex'),
                    'long_text' => trans('admin.billing_categ-ory.columns.long_text'),
                    'published_at' => trans('admin.billing_categ-ory.columns.published_at'),
                    'published_to' => trans('admin.billing_categ-ory.columns.published_to'),
                    'date_start' => trans('admin.billing_categ-ory.columns.date_start'),
                    'time_start' => trans('admin.billing_categ-ory.columns.time_start'),
                    'date_time_end' => trans('admin.billing_categ-ory.columns.date_time_end'),
                    'released_at' => trans('admin.billing_categ-ory.columns.released_at'),
                    'text' => trans('admin.billing_categ-ory.columns.text'),
                    'description' => trans('admin.billing_categ-ory.columns.description'),
                    'enabled' => trans('admin.billing_categ-ory.columns.enabled'),
                    'send' => trans('admin.billing_categ-ory.columns.send'),
                    'price' => trans('admin.billing_categ-ory.columns.price'),
                    'rating' => trans('admin.billing_categ-ory.columns.rating'),
                    'views' => trans('admin.billing_categ-ory.columns.views'),
                    'created_by_admin_user_id' => trans('admin.billing_categ-ory.columns.created_by_admin_user_id'),
                    'updated_by_admin_user_id' => trans('admin.billing_categ-ory.columns.updated_by_admin_user_id'),
                    'created_at' => trans('admin.billing_categ-ory.columns.created_at'),
                    'updated_at' => trans('admin.billing_categ-ory.columns.updated_at'),
                ],
                'back' => trans('brackets/admin-ui::admin.btn.back'),
            ]) }}"
            v-cloak
        ></billing-categ-ory-show>

    </div>

@endsection
