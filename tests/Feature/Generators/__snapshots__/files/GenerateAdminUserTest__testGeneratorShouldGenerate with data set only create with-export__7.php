@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.admin-user.actions.index'))

@section('body')

    <admin-user-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :create-url="'{{ $createUrl }}'"
        :export-url="'{{ $exportUrl }}'"
        :resend-activation-url-template="'{{ $resendActivationUrlTemplate }}'"
        :impersonal-login-url-template="'{{ $impersonalLoginUrlTemplate }}'"
        :activation="!!'{{ $activation }}'"
        :can-impersonal-login="{{ json_encode($canImpersonalLogin) }}"
        :translations="{{ json_encode([
            'listing_title' => trans('admin.admin-user.actions.index'),
            'create_btn' => trans('admin.admin-user.actions.create'),
            'export_btn' => trans('admin.admin-user.actions.export'),
            'columns' => [
                'id' => trans('admin.admin-user.columns.id'),
                'first_name' => trans('admin.admin-user.columns.first_name'),
                'last_name' => trans('admin.admin-user.columns.last_name'),
                'email' => trans('admin.admin-user.columns.email'),
                'activated' => trans('admin.admin-user.columns.activated'),
                'forbidden' => trans('admin.admin-user.columns.forbidden'),
                'language' => trans('admin.admin-user.columns.language'),
            ],
            'impersonal_login_btn' => trans('brackets/admin-ui::admin.operation.impersonal_login'),
            'resend_activation_btn' => trans('brackets/admin-ui::admin.operation.resend_activation'),
            'search_placeholder' => trans('brackets/admin-ui::admin.placeholder.search'),
            'search_btn' => trans('brackets/admin-ui::admin.btn.search'),
            'overview' => trans('brackets/admin-ui::admin.pagination.overview'),
            'previous' => trans('brackets/admin-ui::admin.pagination.previous'),
            'next' => trans('brackets/admin-ui::admin.pagination.next'),
            'no_items' => trans('brackets/admin-ui::admin.index.no_items'),
            'try_changing_items' => trans('brackets/admin-ui::admin.index.try_changing_filters_or_add_new'),
        ]) }}"
        v-cloak
    ></admin-user-listing>

@endsection
