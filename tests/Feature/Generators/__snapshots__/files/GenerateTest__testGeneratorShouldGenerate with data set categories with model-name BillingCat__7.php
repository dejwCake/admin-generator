@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_cat.actions.index'))

@section('body')

    <billing-cat-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :create-url="'{{ $createUrl }}'"
        :edit-url-template="'{{ $editUrlTemplate }}'"
        :update-url-template="'{{ $updateUrlTemplate }}'"
        :destroy-url-template="'{{ $destroyUrlTemplate }}'"
        :bulk-all-url="'{{ $bulkAllUrl }}'"
        :bulk-destroy-url="'{{ $bulkDestroyUrl }}'"
        :translations="{{ json_encode([
            'listing_title' => trans('admin.billing_cat.actions.index'),
            'create_btn' => trans('admin.billing_cat.actions.create'),
            'columns' => [
                'id' => trans('admin.billing_cat.columns.id'),
                'user_id' => trans('admin.billing_cat.columns.user_id'),
                'title' => trans('admin.billing_cat.columns.title'),
                'name' => trans('admin.billing_cat.columns.name'),
                'first_name' => trans('admin.billing_cat.columns.first_name'),
                'last_name' => trans('admin.billing_cat.columns.last_name'),
                'subject' => trans('admin.billing_cat.columns.subject'),
                'email' => trans('admin.billing_cat.columns.email'),
                'language' => trans('admin.billing_cat.columns.language'),
                'published_at' => trans('admin.billing_cat.columns.published_at'),
                'published_to' => trans('admin.billing_cat.columns.published_to'),
                'date_start' => trans('admin.billing_cat.columns.date_start'),
                'time_start' => trans('admin.billing_cat.columns.time_start'),
                'date_time_end' => trans('admin.billing_cat.columns.date_time_end'),
                'released_at' => trans('admin.billing_cat.columns.released_at'),
                'enabled' => trans('admin.billing_cat.columns.enabled'),
                'send' => trans('admin.billing_cat.columns.send'),
                'price' => trans('admin.billing_cat.columns.price'),
                'rating' => trans('admin.billing_cat.columns.rating'),
                'views' => trans('admin.billing_cat.columns.views'),
                'created_by_admin_user_id' => trans('admin.billing_cat.columns.created_by_admin_user_id'),
                'updated_by_admin_user_id' => trans('admin.billing_cat.columns.updated_by_admin_user_id'),
            ],
            'search_placeholder' => trans('brackets/admin-ui::admin.placeholder.search'),
            'search_btn' => trans('brackets/admin-ui::admin.btn.search'),
            'edit_btn' => trans('brackets/admin-ui::admin.btn.edit'),
            'delete_btn' => trans('brackets/admin-ui::admin.btn.delete'),
            'confirm_delete_title' => trans('brackets/admin-ui::admin.operation.confirm_delete_title'),
            'confirm_delete_text' => trans('brackets/admin-ui::admin.operation.confirm_delete_text'),
            'confirm_delete_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_delete_text_multiple'),
            'cancel_btn' => trans('brackets/admin-ui::admin.btn.cancel'),
            'selected_items' => trans('brackets/admin-ui::admin.listing.selected_items'),
            'check_all_items' => trans('brackets/admin-ui::admin.listing.check_all_items'),
            'uncheck_all_items' => trans('brackets/admin-ui::admin.listing.uncheck_all_items'),
            'overview' => trans('brackets/admin-ui::admin.pagination.overview'),
            'previous' => trans('brackets/admin-ui::admin.pagination.previous'),
            'next' => trans('brackets/admin-ui::admin.pagination.next'),
            'no_items' => trans('brackets/admin-ui::admin.index.no_items'),
            'try_changing_items' => trans('brackets/admin-ui::admin.index.try_changing_items'),
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
    ></billing-cat-listing>

@endsection
