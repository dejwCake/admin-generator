@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.category.actions.index'))

@section('body')

    <category-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :edit-url-template="'{{ $editUrlTemplate }}'"
        :update-url-template="'{{ $updateUrlTemplate }}'"
        :translations="{{ json_encode([
            'listing_title' => trans('admin.category.actions.index'),
            'columns' => [
                'id' => trans('admin.category.columns.id'),
                'uuid' => trans('admin.category.columns.uuid'),
                'user_id' => trans('admin.category.columns.user_id'),
                'title' => trans('admin.category.columns.title'),
                'name' => trans('admin.category.columns.name'),
                'first_name' => trans('admin.category.columns.first_name'),
                'last_name' => trans('admin.category.columns.last_name'),
                'subject' => trans('admin.category.columns.subject'),
                'email' => trans('admin.category.columns.email'),
                'language' => trans('admin.category.columns.language'),
                'published_at' => trans('admin.category.columns.published_at'),
                'published_to' => trans('admin.category.columns.published_to'),
                'date_start' => trans('admin.category.columns.date_start'),
                'time_start' => trans('admin.category.columns.time_start'),
                'date_time_end' => trans('admin.category.columns.date_time_end'),
                'released_at' => trans('admin.category.columns.released_at'),
                'enabled' => trans('admin.category.columns.enabled'),
                'send' => trans('admin.category.columns.send'),
                'price' => trans('admin.category.columns.price'),
                'rating' => trans('admin.category.columns.rating'),
                'views' => trans('admin.category.columns.views'),
                'created_by_admin_user_id' => trans('admin.category.columns.created_by_admin_user_id'),
                'updated_by_admin_user_id' => trans('admin.category.columns.updated_by_admin_user_id'),
            ],
            'search_placeholder' => trans('brackets/admin-ui::admin.placeholder.search'),
            'search_btn' => trans('brackets/admin-ui::admin.btn.search'),
            'edit_btn' => trans('brackets/admin-ui::admin.btn.edit'),
            'overview' => trans('brackets/admin-ui::admin.pagination.overview'),
            'previous' => trans('brackets/admin-ui::admin.pagination.previous'),
            'next' => trans('brackets/admin-ui::admin.pagination.next'),
            'no_items' => trans('brackets/admin-ui::admin.index.no_items'),
            'try_changing_items' => trans('brackets/admin-ui::admin.index.try_changing_filters'),
            'publish_now' => trans('brackets/admin-ui::admin.operation.publish_now'),
            'unpublish_now' => trans('brackets/admin-ui::admin.operation.unpublish_now'),
            'publish_later' => trans('brackets/admin-ui::admin.operation.publish_later'),
            'will_be_published' => trans('brackets/admin-ui::admin.operation.will_be_published'),
            'confirm_publish_now' => trans('brackets/admin-ui::admin.operation.confirm_publish_now'),
            'confirm_unpublish_now' => trans('brackets/admin-ui::admin.operation.confirm_unpublish_now'),
            'created_on' => trans('brackets/admin-ui::admin.forms.created_on'),
            'updated_on' => trans('brackets/admin-ui::admin.forms.updated_on'),
        ]) }}"
        v-cloak
    ></category-listing>

@endsection
