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
@if($hasTrash)
        :force-destroy-url-template="'{{'{{'}} $forceDestroyUrlTemplate }}'"
        :restore-url-template="'{{'{{'}} $restoreUrlTemplate }}'"
@endif
@if($hasBulkDelete)
        :bulk-all-url="'{{'{{'}} $bulkAllUrl }}'"
        :bulk-destroy-url="'{{'{{'}} $bulkDestroyUrl }}'"
@endif
@if($hasBulkTrash)
        :bulk-force-destroy-url="'{{'{{'}} $bulkForceDestroyUrl }}'"
        :bulk-restore-url="'{{'{{'}} $bulkRestoreUrl }}'"
@endif
@if($hasExport)
        :export-url="'{{'{{'}} $exportUrl }}'"
@endif
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
@if($hasTrash || $hasBulkTrash)
                'deleted_at' => trans('admin.{{ $modelLangFormat }}.columns.deleted_at'),
@endif
            ],
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
@if($hasTrash || $hasBulkTrash)
            'force_delete_btn' => trans('brackets/admin-ui::admin.btn.force_delete'),
            'restore_btn' => trans('brackets/admin-ui::admin.btn.restore'),
@endif
            'confirm_delete_title' => trans('brackets/admin-ui::admin.operation.confirm_delete_title'),
            'confirm_delete_text' => trans('brackets/admin-ui::admin.operation.confirm_delete_text'),
@endif
@if($hasTrash)
            'confirm_force_delete_text' => trans('brackets/admin-ui::admin.operation.confirm_force_delete_text'),
@endif
@if($hasTrash || $hasBulkTrash)
            'confirm_restore_title' => trans('brackets/admin-ui::admin.operation.confirm_restore_title'),
@endif
@if($hasTrash)
            'confirm_restore_text' => trans('brackets/admin-ui::admin.operation.confirm_restore_text'),
            'not_restorable' => trans('brackets/admin-ui::admin.operation.not_restorable'),
@endif
@if($hasBulkDelete)
            'confirm_delete_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_delete_text_multiple'),
@endif
@if($hasBulkTrash)
            'confirm_force_delete_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_force_delete_text_multiple'),
            'confirm_restore_text_multiple' => trans('brackets/admin-ui::admin.operation.confirm_restore_text_multiple'),
@endif
@if($hasDelete || $hasBulkDelete)
            'cancel_btn' => trans('brackets/admin-ui::admin.btn.cancel'),
@endif
@if($hasBulkDelete)
            'selected_items' => trans('brackets/admin-ui::admin.listing.selected_items'),
            'check_all_items' => trans('brackets/admin-ui::admin.listing.check_all_items'),
            'uncheck_all_items' => trans('brackets/admin-ui::admin.listing.uncheck_all_items'),
@endif
@if($hasTrash || $hasBulkTrash)
            'show_deleted' => trans('brackets/admin-ui::admin.listing.show_deleted'),
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
@if($hasPublishedAt && $hasEdit)
            'publish_now' => trans('brackets/admin-ui::admin.operation.publish_now'),
            'unpublish_now' => trans('brackets/admin-ui::admin.operation.unpublish_now'),
            'publish_later' => trans('brackets/admin-ui::admin.operation.publish_later'),
            'will_be_published' => trans('brackets/admin-ui::admin.operation.will_be_published'),
            'confirm_publish_now' => trans('brackets/admin-ui::admin.operation.confirm_publish_now'),
            'confirm_unpublish_now' => trans('brackets/admin-ui::admin.operation.confirm_unpublish_now'),
@endif
@if($hasCreatedByAdminUser)
            'created_on' => trans('brackets/admin-ui::admin.forms.created_on'),
@endif
@if($hasUpdatedByAdminUser)
            'updated_on' => trans('brackets/admin-ui::admin.forms.updated_on'),
@endif
        ]) }}"
        v-cloak
    ></{{ $modelJSName }}-listing>

{{'@'}}endsection
