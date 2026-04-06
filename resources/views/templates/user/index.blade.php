{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.index'))

{{'@'}}section('body')

    <{{ $modelJSName }}-listing
        :data="{{'{{'}} $data->toJson() }}"
        :url="'{{'{{'}} $url }}'"
        :create-url="'{{'{{'}} $createUrl }}'"
        :edit-url-template="'{{'{{'}} $editUrlTemplate }}'"
        :update-url-template="'{{'{{'}} $updateUrlTemplate }}'"
        :destroy-url-template="'{{'{{'}} $destroyUrlTemplate }}'"
@if($hasBulk)
        :bulk-all-url="'{{'{{'}} $bulkAllUrl }}'"
        :bulk-destroy-url="'{{'{{'}} $bulkDestroyUrl }}'"
@endif
@if($hasExport)
        :export-url="'{{'{{'}} $exportUrl }}'"
@endif
        :resend-verify-email-url-template="'{{'{{'}} $resendVerifyEmailUrlTemplate }}'"
        :translations="{{'{{'}} json_encode([
            'listing_title' => trans('admin.{{ $modelLangFormat }}.actions.index'),
            'create_btn' => trans('admin.{{ $modelLangFormat }}.actions.create'),
@if($hasExport)
            'export_btn' => trans('admin.{{ $modelLangFormat }}.actions.export'),
@endif
            'columns' => [
@foreach($columns as $col)
                '{{ $col['name'] }}' => trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}'),
@endforeach
            ],
            'resend_verify_email_btn' => trans('admin.{{ $modelLangFormat }}.actions.resend_verify_email'),
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
    ></{{ $modelJSName }}-listing>

{{'@'}}endsection
