{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.index'))

{{'@'}}section('body')

    <{{ $modelJSName }}-listing
        :data="{{'{{'}} $data->toJson() }}"
        :url="'{{'{{'}} $url }}'"
@if($hasCreate)
        :create-url="'{{'{{'}} $createUrl }}'"
@endif
@if($hasShow)
        :show-url-template="'{{'{{'}} $showUrlTemplate }}'"
@endif
@if($hasEdit)
        :edit-url-template="'{{'{{'}} $editUrlTemplate }}'"
        :update-url-template="'{{'{{'}} $updateUrlTemplate }}'"
@endif
@if($hasDelete)
        :destroy-url-template="'{{'{{'}} $destroyUrlTemplate }}'"
@endif
@if($hasBulkDelete)
        :bulk-all-url="'{{'{{'}} $bulkAllUrl }}'"
        :bulk-destroy-url="'{{'{{'}} $bulkDestroyUrl }}'"
@endif
@if($hasExport)
        :export-url="'{{'{{'}} $exportUrl }}'"
@endif
        :resend-verify-email-url-template="'{{'{{'}} $resendVerifyEmailUrlTemplate }}'"
        :impersonal-login-url-template="'{{'{{'}} $impersonalLoginUrlTemplate }}'"
        :can-impersonal-login="{{'{{'}} json_encode($canImpersonalLogin) }}"
        :translations="{{'{{'}} json_encode([
            'listing_title' => trans('admin.{{ $modelLangFormat }}.actions.index'),
@if($hasCreate)
            'create_btn' => trans('admin.{{ $modelLangFormat }}.actions.create'),
@endif
@if($hasExport)
            'export_btn' => trans('admin.{{ $modelLangFormat }}.actions.export'),
@endif
            'columns' => [
@foreach($columns as $column)
                '{{ $column->name }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ $column->name }}'),
@endforeach
            ],
            'resend_verify_email_btn' => trans('admin.{{ $modelLangFormat }}.actions.resend_verify_email'),
            'impersonal_login_btn' => trans('brackets/admin-ui::admin.operation.impersonal_login'),
            'search_placeholder' => trans('brackets/admin-ui::admin.placeholder.search'),
            'search_btn' => trans('brackets/admin-ui::admin.btn.search'),
@if($hasShow)
            'show_btn' => trans('brackets/admin-ui::admin.btn.show'),
@endif
@if($hasEdit)
            'edit_btn' => trans('brackets/admin-ui::admin.btn.edit'),
@endif
@if($hasDelete || $hasBulkDelete)
            'delete_btn' => trans('brackets/admin-ui::admin.btn.delete'),
            'confirm_delete_title' => trans('brackets/admin-ui::admin.operation.confirm_delete_title'),
            'confirm_delete_text' => trans('brackets/admin-ui::admin.operation.confirm_delete_text'),
@endif
@if($hasBulkDelete)
            'confirm_delete_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_delete_text_multiple'),
@endif
@if($hasDelete || $hasBulkDelete)
            'cancel_btn' => trans('brackets/admin-ui::admin.btn.cancel'),
@endif
@if($hasBulkDelete)
            'selected_items' => trans('brackets/admin-ui::admin.listing.selected_items'),
            'check_all_items' => trans('brackets/admin-ui::admin.listing.check_all_items'),
            'uncheck_all_items' => trans('brackets/admin-ui::admin.listing.uncheck_all_items'),
@endif
            'overview' => trans('brackets/admin-ui::admin.pagination.overview'),
            'previous' => trans('brackets/admin-ui::admin.pagination.previous'),
            'next' => trans('brackets/admin-ui::admin.pagination.next'),
            'no_items' => trans('brackets/admin-ui::admin.index.no_items'),
@if($hasCreate)
            'try_changing_items' => trans('brackets/admin-ui::admin.index.try_changing_filters_or_add_new'),
@else
            'try_changing_items' => trans('brackets/admin-ui::admin.index.try_changing_filters'),
@endif
        ]) }}"
        v-cloak
    ></{{ $modelJSName }}-listing>

{{'@'}}endsection
