@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.user.actions.index'))

@section('body')

    <user-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :create-url="'{{ $createUrl }}'"
        :show-url-template="'{{ $showUrlTemplate }}'"
        :edit-url-template="'{{ $editUrlTemplate }}'"
        :update-url-template="'{{ $updateUrlTemplate }}'"
        :destroy-url-template="'{{ $destroyUrlTemplate }}'"
        :resend-verify-email-url-template="'{{ $resendVerifyEmailUrlTemplate }}'"
        :impersonal-login-url-template="'{{ $impersonalLoginUrlTemplate }}'"
        :can-impersonal-login="{{ json_encode($canImpersonalLogin) }}"
        :translations="{{ json_encode([
            'listing_title' => trans('admin.user.actions.index'),
            'create_btn' => trans('admin.user.actions.create'),
            'columns' => [
                'id' => trans('admin.user.columns.id'),
                'name' => trans('admin.user.columns.name'),
                'email' => trans('admin.user.columns.email'),
                'email_verified_at' => trans('admin.user.columns.email_verified_at'),
            ],
            'resend_verify_email_btn' => trans('admin.user.actions.resend_verify_email'),
            'impersonal_login_btn' => trans('brackets/admin-ui::admin.operation.impersonal_login'),
            'search_placeholder' => trans('brackets/admin-ui::admin.placeholder.search'),
            'search_btn' => trans('brackets/admin-ui::admin.btn.search'),
            'show_btn' => trans('brackets/admin-ui::admin.btn.show'),
            'edit_btn' => trans('brackets/admin-ui::admin.btn.edit'),
            'delete_btn' => trans('brackets/admin-ui::admin.btn.delete'),
            'confirm_delete_title' => trans('brackets/admin-ui::admin.operation.confirm_delete_title'),
            'confirm_delete_text' => trans('brackets/admin-ui::admin.operation.confirm_delete_text'),
            'cancel_btn' => trans('brackets/admin-ui::admin.btn.cancel'),
            'overview' => trans('brackets/admin-ui::admin.pagination.overview'),
            'previous' => trans('brackets/admin-ui::admin.pagination.previous'),
            'next' => trans('brackets/admin-ui::admin.pagination.next'),
            'no_items' => trans('brackets/admin-ui::admin.index.no_items'),
            'try_changing_items' => trans('brackets/admin-ui::admin.index.try_changing_filters_or_add_new'),
        ]) }}"
        v-cloak
    ></user-listing>

@endsection
