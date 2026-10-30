@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.cat.actions.index'))

@section('body')

    <cat-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :create-url="'{{ $createUrl }}'"
        :show-url-template="'{{ $showUrlTemplate }}'"
        :edit-url-template="'{{ $editUrlTemplate }}'"
        :update-url-template="'{{ $updateUrlTemplate }}'"
        :destroy-url-template="'{{ $destroyUrlTemplate }}'"
        :force-destroy-url-template="'{{ $forceDestroyUrlTemplate }}'"
        :restore-url-template="'{{ $restoreUrlTemplate }}'"
        :bulk-all-url="'{{ $bulkAllUrl }}'"
        :bulk-destroy-url="'{{ $bulkDestroyUrl }}'"
        :bulk-force-destroy-url="'{{ $bulkForceDestroyUrl }}'"
        :bulk-restore-url="'{{ $bulkRestoreUrl }}'"
        :translations="{{ json_encode([
            'listing_title' => trans('admin.cat.actions.index'),
            'create_btn' => trans('admin.cat.actions.create'),
            'columns' => [
                'id' => trans('admin.cat.columns.id'),
                'uuid' => trans('admin.cat.columns.uuid'),
                'user_id' => trans('admin.cat.columns.user_id'),
                'title' => trans('admin.cat.columns.title'),
                'name' => trans('admin.cat.columns.name'),
                'first_name' => trans('admin.cat.columns.first_name'),
                'last_name' => trans('admin.cat.columns.last_name'),
                'subject' => trans('admin.cat.columns.subject'),
                'email' => trans('admin.cat.columns.email'),
                'language' => trans('admin.cat.columns.language'),
                'published_at' => trans('admin.cat.columns.published_at'),
                'published_to' => trans('admin.cat.columns.published_to'),
                'date_start' => trans('admin.cat.columns.date_start'),
                'time_start' => trans('admin.cat.columns.time_start'),
                'date_time_end' => trans('admin.cat.columns.date_time_end'),
                'released_at' => trans('admin.cat.columns.released_at'),
                'enabled' => trans('admin.cat.columns.enabled'),
                'send' => trans('admin.cat.columns.send'),
                'price' => trans('admin.cat.columns.price'),
                'rating' => trans('admin.cat.columns.rating'),
                'views' => trans('admin.cat.columns.views'),
                'created_by_admin_user_id' => trans('admin.cat.columns.created_by_admin_user_id'),
                'updated_by_admin_user_id' => trans('admin.cat.columns.updated_by_admin_user_id'),
                'deleted_at' => trans('admin.cat.columns.deleted_at'),
            ],
            'search_placeholder' => trans('brackets/admin-ui::admin.placeholder.search'),
            'search_btn' => trans('brackets/admin-ui::admin.btn.search'),
            'show_btn' => trans('brackets/admin-ui::admin.btn.show'),
            'edit_btn' => trans('brackets/admin-ui::admin.btn.edit'),
            'delete_btn' => trans('brackets/admin-ui::admin.btn.delete'),
            'force_delete_btn' => trans('brackets/admin-ui::admin.btn.force_delete'),
            'restore_btn' => trans('brackets/admin-ui::admin.btn.restore'),
            'confirm_delete_title' => trans('brackets/admin-ui::admin.operation.confirm_delete_title'),
            'confirm_delete_text' => trans('brackets/admin-ui::admin.operation.confirm_delete_text'),
            'confirm_force_delete_text' => trans('brackets/admin-ui::admin.operation.confirm_force_delete_text'),
            'confirm_restore_title' => trans('brackets/admin-ui::admin.operation.confirm_restore_title'),
            'confirm_restore_text' => trans('brackets/admin-ui::admin.operation.confirm_restore_text'),
            'not_restorable' => trans('brackets/admin-ui::admin.operation.not_restorable'),
            'confirm_delete_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_delete_text_multiple'),
            'confirm_force_delete_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_force_delete_text_multiple'),
            'confirm_restore_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_restore_text_multiple'),
            'cancel_btn' => trans('brackets/admin-ui::admin.btn.cancel'),
            'selected_items' => trans('brackets/admin-ui::admin.listing.selected_items'),
            'check_all_items' => trans('brackets/admin-ui::admin.listing.check_all_items'),
            'uncheck_all_items' => trans('brackets/admin-ui::admin.listing.uncheck_all_items'),
            'show_deleted' => trans('brackets/admin-ui::admin.listing.show_deleted'),
            'overview' => trans('brackets/admin-ui::admin.pagination.overview'),
            'previous' => trans('brackets/admin-ui::admin.pagination.previous'),
            'next' => trans('brackets/admin-ui::admin.pagination.next'),
            'no_items' => trans('brackets/admin-ui::admin.index.no_items'),
            'try_changing_items' => trans('brackets/admin-ui::admin.index.try_changing_filters_or_add_new'),
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
    ></cat-listing>

@endsection
