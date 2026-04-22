@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.category.actions.index'))

@section('body')

    <category-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :create-url="'{{ $createUrl }}'"
        :edit-url-template="'{{ $editUrlTemplate }}'"
        :update-url-template="'{{ $updateUrlTemplate }}'"
        :destroy-url-template="'{{ $destroyUrlTemplate }}'"
        :bulk-all-url="'{{ $bulkAllUrl }}'"
        :bulk-destroy-url="'{{ $bulkDestroyUrl }}'"
        :resend-activation-url-template="'{{ $resendActivationUrlTemplate }}'"
        :impersonal-login-url-template="'{{ $impersonalLoginUrlTemplate }}'"
        :activation="!!'{{ $activation }}'"
        :can-impersonal-login="{{ json_encode($canImpersonalLogin) }}"
        :translations="{{ json_encode([
            'listing_title' => trans('admin.category.actions.index'),
            'create_btn' => trans('admin.category.actions.create'),
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
                'long_text' => trans('admin.category.columns.long_text'),
                'published_at' => trans('admin.category.columns.published_at'),
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
            'impersonal_login_btn' => trans('brackets/admin-ui::admin.operation.impersonal_login'),
            'resend_activation_btn' => trans('brackets/admin-ui::admin.operation.resend_activation'),
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
        ]) }}"
        v-cloak
    ></category-listing>

@endsection
