@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_categ-ory.actions.index'))

@section('body')

    <billing-categ-ory-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :create-url="'{{ $createUrl }}'"
        :edit-url-template="'{{ $editUrlTemplate }}'"
        :update-url-template="'{{ $updateUrlTemplate }}'"
        :destroy-url-template="'{{ $destroyUrlTemplate }}'"
        :bulk-all-url="'{{ $bulkAllUrl }}'"
        :bulk-destroy-url="'{{ $bulkDestroyUrl }}'"
        :translations="{{ json_encode([
            'listing_title' => trans('admin.billing_categ-ory.actions.index'),
            'create_btn' => trans('admin.billing_categ-ory.actions.create'),
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
                'published_at' => trans('admin.billing_categ-ory.columns.published_at'),
                'published_to' => trans('admin.billing_categ-ory.columns.published_to'),
                'date_start' => trans('admin.billing_categ-ory.columns.date_start'),
                'time_start' => trans('admin.billing_categ-ory.columns.time_start'),
                'date_time_end' => trans('admin.billing_categ-ory.columns.date_time_end'),
                'released_at' => trans('admin.billing_categ-ory.columns.released_at'),
                'enabled' => trans('admin.billing_categ-ory.columns.enabled'),
                'send' => trans('admin.billing_categ-ory.columns.send'),
                'price' => trans('admin.billing_categ-ory.columns.price'),
                'rating' => trans('admin.billing_categ-ory.columns.rating'),
                'views' => trans('admin.billing_categ-ory.columns.views'),
                'created_by_admin_user_id' => trans('admin.billing_categ-ory.columns.created_by_admin_user_id'),
                'updated_by_admin_user_id' => trans('admin.billing_categ-ory.columns.updated_by_admin_user_id'),
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
    ></billing-categ-ory-listing>

@endsection
