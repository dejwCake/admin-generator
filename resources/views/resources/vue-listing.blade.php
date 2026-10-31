@php
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Str;
    assert($relations instanceof RelationCollection);
@endphp
<template>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <ListingHeader
@if($hasCreate)
                        :createUrl="createUrl"
@endif
@if($hasExport)
                        :exportUrl="exportUrl"
@endif
                        :translations="translations"
                    />
                </div>
                <div class="card-body" ref="cardBody">
                    <div class="row justify-content-md-between">
                        <div class="col col-lg-7 col-xl-5 mb-3">
                            <Search
                                v-model:search="search"
                                :filterFn="filter"
                                :translations="translations"
                            />
                        </div>
@if($hasTrash || $hasBulkTrash)
                        <div class="col-auto mb-3 d-flex align-items-center me-auto">
                            <ShowDeleted
                                v-model="showDeleted"
                                :translations="translations"
                                @update:modelValue="onShowDeletedChange"
                            />
                        </div>
@endif
                        <div class="col-sm-auto mb-3">
                            <PerPage v-model="pagination.state.per_page"/>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-listing">
                            <thead>
                            <tr>
@if($hasBulkDelete)
                                <th class="bulk-checkbox">
                                    <BulkCheckboxHeader :isClickedAll="isClickedAll"
                                                        :onToggleAll="onBulkItemsClickedAllWithPagination"/>
                                </th>
@endif

@foreach($columns as $column)
@if($column->name === 'published_at')
                                <Sortable v-if="isColumnVisible({{ $column->priority }})" :column="'{{ $column->name }}'" class="text-center" :orderBy="orderBy" @sort="onSort">
                                    {{ '{{' }} translations.columns.{{ $column->name }} }}
                                </Sortable>
@else
                                <Sortable v-if="isColumnVisible({{ $column->priority }})" :column="'{{ $column->name }}'" :orderBy="orderBy" @sort="onSort">
                                    {{ '{{' }} translations.columns.{{ $column->name }} }}
                                </Sortable>
@endif
@endforeach
@if($hasTrash || $hasBulkTrash)
                                <Sortable v-if="showDeleted && isColumnVisible(0)" :column="'deleted_at'" :orderBy="orderBy" @sort="onSort">
                                    {{ '{{' }} translations.columns.deleted_at }}
                                </Sortable>
@endif

                                <th></th>
                            </tr>
@if($hasBulkDelete)
                            <tr v-show="clickedBulkItemsCount > 0 || isClickedAll">
                                <td class="bg-bulk-info d-table-cell text-center" :colspan="99">
                                    <BulkOperationsBar
                                        :selectedCount="clickedBulkItemsCount"
                                        :totalCount="pagination.state.total"
                                        :loading="bulkCheckingAllLoader"
                                        :onCheckAll="() => onBulkItemsClickedAll(bulkAllUrl)"
                                        :onUncheckAll="onBulkItemsClickedAllUncheck"
@if($hasBulkTrash)
                                        :onBulkDelete="showDeleted ? null : () => bulkDelete(bulkDestroyUrl, {
                                            ...translations,
                                            confirm_title: translations.confirm_delete_title,
                                            confirm_text: translations.confirm_delete_text_multiple,
                                            confirm_btn: translations.delete_btn,
                                        })"
                                        :onBulkForceDelete="() => bulkForceDelete(bulkForceDestroyUrl, {
                                            ...translations,
                                            confirm_title: translations.confirm_delete_title,
                                            confirm_text: translations.confirm_force_delete_text_multiple,
                                            confirm_btn: translations.force_delete_btn,
                                        })"
                                        :onBulkRestore="showDeleted ? () => bulkRestore(bulkRestoreUrl, {
                                            ...translations,
                                            confirm_title: translations.confirm_restore_title,
                                            confirm_text: translations.confirm_restore_text_multiple,
                                            confirm_btn: translations.restore_btn,
                                        }) : null"
@else
                                        :onBulkDelete="() => bulkDelete(bulkDestroyUrl, {
                                            ...translations,
                                            confirm_title: translations.confirm_delete_title,
                                            confirm_text: translations.confirm_delete_text_multiple,
                                            confirm_btn: translations.delete_btn,
                                        })"
@endif
                                        :translations="translations"
                                    />
                                </td>
                            </tr>
@endif
                            </thead>
                            <tbody>
                            <tr
                                v-for="(item, index) in collection"
                                :key="item.id"
@if($hasBulkDelete)
                                :class="bulkItems[item.id] ? 'bg-bulk' : ''"
@endif
                            >
@if($hasBulkDelete)
                                <td class="bulk-checkbox">
                                    <BulkCheckboxRow :itemId="item.id" :checked="!!bulkItems[item.id]"
                                                     :disabled="bulkCheckingAllLoader" :onToggle="onBulkItemClicked"/>
                                </td>
@endif

@foreach($columns as $column)
@if($column->name === 'published_at')
                                <td v-if="isColumnVisible({{ $column->priority }})" class="text-center text-nowrap" style="position: relative;">
@if($hasEdit && ($hasTrash || $hasBulkTrash))
                                    <template v-if="showDeleted">{{ '{{' }} formatDatetime(item.{{ $column->name }}) }}</template>
                                    <PublishedAtColumn
                                        v-else
                                        :item="item"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        :now="now"
                                        :translations="translations"
                                        @update:item="onUpdateItem"
                                    />
@elseif($hasEdit)
                                    <PublishedAtColumn
                                        :item="item"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        :now="now"
                                        :translations="translations"
                                        @update:item="onUpdateItem"
                                    />
@else
                                    {{ '{{' }} formatDatetime(item.{{ $column->name }}) }}
@endif
                                </td>
@elseif($column->name === 'created_by_admin_user_id')
                                <td v-if="isColumnVisible({{ $column->priority }})">
                                    <UserDetailTooltip
                                        v-if="item.created_by_admin_user"
                                        :user="item.created_by_admin_user"
                                        :datetime-text="translations.created_on + ' ' + formatDatetime(item.created_at)"
                                    />
                                </td>
@elseif($column->name === 'updated_by_admin_user_id')
                                <td v-if="isColumnVisible({{ $column->priority }})">
                                    <UserDetailTooltip
                                        v-if="item.updated_by_admin_user"
                                        :user="item.updated_by_admin_user"
                                        :datetime-text="translations.updated_on + ' ' + formatDatetime(item.updated_at)"
                                    />
                                </td>
@elseif(in_array($column->majorType, ['bool'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">
@if($hasEdit && ($hasTrash || $hasBulkTrash))
                                    <ReadOnlySwitch v-if="showDeleted" :modelValue="item.{{ $column->name }}"/>
                                    <ToggleSwitch
                                        v-else
                                        v-model="collection[index].{{ $column->name }}"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="{{ $column->name }}"
                                        :row="collection[index]"
                                    />
@elseif($hasEdit)
                                    <ToggleSwitch
                                        v-model="collection[index].{{ $column->name }}"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="{{ $column->name }}"
                                        :row="collection[index]"
                                    />
@else
                                    <ReadOnlySwitch :modelValue="item.{{ $column->name }}"/>
@endif
                                </td>
@elseif(in_array($column->majorType, ['date'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} formatDate(item.{{ $column->name }}) }}</td>
@elseif(in_array($column->majorType, ['time'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} formatTime(item.{{ $column->name }}) }}</td>
@elseif(in_array($column->majorType, ['datetime'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} formatDatetime(item.{{ $column->name }}) }}</td>
@elseif($column->isArray())
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} Object.values(item.{{ $column->name }} ?? {}).join(', ') }}</td>
@elseif($column->isForeignKey && $relations->hasBelongsToByColumn($column->name))
@php $belongsTo = $relations->getBelongsToByColumn($column->name); @endphp
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} item.{{ Str::snake($belongsTo->relationMethodName) }}?.{{ $belongsTo->relatedLabel }} }}</td>
@else
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} item.{{ $column->name }} }}</td>
@endif
@endforeach
@if($hasTrash || $hasBulkTrash)
                                <td v-if="showDeleted && isColumnVisible(0)">{{ '{{' }} formatDatetime(item.deleted_at) }}</td>
@endif

                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
@if($hasTrash || $hasBulkTrash)
@if($hasTrash)
                                        <RestoreButton
                                            v-if="showDeleted"
                                            :disabled="item.restorable === false"
                                            :url="resolveUrl(restoreUrlTemplate, item)"
                                            :translations="{
                                              ...translations,
                                              confirm_title: translations.confirm_restore_title,
                                              confirm_text: translations.confirm_restore_text,
                                              confirm_btn: translations.restore_btn,
                                            }"
                                            @restored="loadData"
                                        />
@endif
@if($hasShow || $hasEdit || $hasDelete)
@if($hasTrash)
                                        <template v-else>
@else
                                        <template v-if="!showDeleted">
@endif
@if($hasShow)
                                            <ShowButton
                                                :url="resolveUrl(showUrlTemplate, item)"
                                                :translations="translations"
                                            />
@endif
@if($hasEdit)
                                            <EditButton
                                                :url="resolveUrl(editUrlTemplate, item)"
                                                :translations="translations"
                                            />
@endif
@if($hasDelete)
                                            <DeleteButton
                                                :url="resolveUrl(destroyUrlTemplate, item)"
                                                :translations="{
                                                  ...translations,
                                                  confirm_title: translations.confirm_delete_title,
                                                  confirm_text: translations.confirm_delete_text,
                                                  confirm_btn: translations.delete_btn,
                                                }"
                                                @deleted="loadData"
                                            />
@endif
                                        </template>
@endif
@if($hasTrash)
                                        <ForceDeleteButton
                                            :url="resolveUrl(forceDestroyUrlTemplate, item)"
                                            :translations="{
                                              ...translations,
                                              confirm_title: translations.confirm_delete_title,
                                              confirm_text: translations.confirm_force_delete_text,
                                              confirm_btn: translations.force_delete_btn,
                                            }"
                                            @deleted="loadData"
                                        />
@endif
@else
@if($hasShow)
                                        <ShowButton
                                            :url="resolveUrl(showUrlTemplate, item)"
                                            :translations="translations"
                                        />
@endif
@if($hasEdit)
                                        <EditButton
                                            :url="resolveUrl(editUrlTemplate, item)"
                                            :translations="translations"
                                        />
@endif
@if($hasDelete)
                                        <DeleteButton
                                            :url="resolveUrl(destroyUrlTemplate, item)"
                                            :translations="{
                                              ...translations,
                                              confirm_title: translations.confirm_delete_title,
                                              confirm_text: translations.confirm_delete_text,
                                              confirm_btn: translations.delete_btn,
                                            }"
                                            @deleted="loadData"
                                        />
@endif
@endif
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        :pagination="pagination.state"
                        :options="pagination.options"
                        :translations="translations"
                        @page-change="onPageChange"
                        @per-page-change="onPerPageChange"
                    />

                    <EmptyState
                        :show="!collection?.length"
@if($hasCreate)
                        :createUrl="createUrl"
@endif
                        :translations="translations"
                    />
                </div>
            </div>
        </div>

@if($hasBulkDelete)
        <ConfirmModal
            :show="confirmModal.show"
@if($hasBulkTrash)
            :variant="confirmModal.variant"
@endif
            :translations="confirmModal.translations"
            @confirm="confirmModal.onConfirm"
            @cancel="confirmModal.show = false"
        />
@endif
    </div>
</template>

@php
    use Illuminate\Support\Collection;
@endphp
@php
    $imports = new Collection([
        "{ref} from 'vue'",
        "{useAppListing} from '../composables/useAppListing.js'",
        "{useResponsiveColumns} from '@craftable/composables/useResponsiveColumns.js'",
    ]);
    if ($hasDateColumns) {
        $imports->push('{' . $dateImports->implode(', ') . "} from '@craftable/utils/dateFormatters.js'");
    }
    $imports->push("Sortable from '@craftable/components/listing/Sortable.vue'");
    $imports->push("Pagination from '@craftable/components/listing/Pagination.vue'");
    $imports->push("Search from '@craftable/components/listing/Search.vue'");
    $imports->push("PerPage from '@craftable/components/listing/PerPage.vue'");
    $imports->push("EmptyState from '@craftable/components/listing/EmptyState.vue'");
    if ($hasBulkDelete) {
        $imports->push("BulkCheckboxHeader from '@craftable/components/listing/BulkCheckboxHeader.vue'");
        $imports->push("BulkCheckboxRow from '@craftable/components/listing/BulkCheckboxRow.vue'");
        $imports->push("BulkOperationsBar from '@craftable/components/listing/BulkOperationsBar.vue'");
    }
    if ($hasPublishedAt && $hasEdit) {
        $imports->push("PublishedAtColumn from '@craftable/components/listing/PublishedAtColumn.vue'");
    }
    $imports->push("ListingHeader from '@craftable/components/listing/ListingHeader.vue'");
    if ($hasShow) {
        $imports->push("ShowButton from '@craftable/components/listing/ShowButton.vue'");
    }
    if ($hasEdit) {
        $imports->push("EditButton from '@craftable/components/listing/EditButton.vue'");
    }
    if ($hasDelete) {
        $imports->push("DeleteButton from '@craftable/components/listing/DeleteButton.vue'");
    }
    if ($hasTrash || $hasBulkTrash) {
        $imports->push("ShowDeleted from '@craftable/components/listing/ShowDeleted.vue'");
    }
    if ($hasTrash) {
        $imports->push("ForceDeleteButton from '@craftable/components/listing/ForceDeleteButton.vue'");
        $imports->push("RestoreButton from '@craftable/components/listing/RestoreButton.vue'");
    }
    if ($hasUserDetailTooltip) {
        $imports->push("UserDetailTooltip from '@craftable/components/UserDetailTooltip.vue'");
    }
    if ($hasSwitchColumns) {
        $imports->push(
            $hasEdit
                ? "ToggleSwitch from '@craftable/components/listing/ToggleSwitch.vue'"
                : "ReadOnlySwitch from '@craftable/components/ReadOnlySwitch.vue'",
        );
    }
    if ($hasSwitchColumns && $hasEdit && ($hasTrash || $hasBulkTrash)) {
        $imports->push("ReadOnlySwitch from '@craftable/components/ReadOnlySwitch.vue'");
    }
    if ($hasBulkDelete) {
        $imports->push("ConfirmModal from '@craftable/components/ConfirmModal.vue'");
    }
    $imports = $imports->unique()->sort();
@endphp
<script setup>
@foreach($imports as $import)
import {!! $import !!};
@endforeach

const props = defineProps({
    url: {type: String, required: true},
    data: {type: Object, default: null},
    timezone: {type: String, default: 'UTC'},
    translations: {type: Object, default: () => ({})},
@if($hasCreate)
    createUrl: {type: String, default: ''},
@endif
@if($hasExport)
    exportUrl: {type: String, default: ''},
@endif
@if($hasShow)
    showUrlTemplate: {type: String, required: true},
@endif
@if($hasEdit)
    editUrlTemplate: {type: String, required: true},
    updateUrlTemplate: {type: String, required: true},
@endif
@if($hasDelete)
    destroyUrlTemplate: {type: String, required: true},
@endif
@if($hasTrash)
    forceDestroyUrlTemplate: {type: String, required: true},
    restoreUrlTemplate: {type: String, required: true},
@endif
@if($hasBulkDelete)
    bulkAllUrl: {type: String, default: ''},
    bulkDestroyUrl: {type: String, default: ''},
@endif
@if($hasBulkTrash)
    bulkForceDestroyUrl: {type: String, default: ''},
    bulkRestoreUrl: {type: String, default: ''},
@endif
});

const cardBody = ref(null);
const {isColumnVisible} = useResponsiveColumns(cardBody);

const {
    pagination, orderBy, filters, search, collection, now,
@if($hasBulkDelete)
    bulkItems, bulkCheckingAllLoader,
    isClickedAll, clickedBulkItemsCount, loadData, filter, resolveUrl,
    onBulkItemClicked,
    onBulkItemsClickedAll, onBulkItemsClickedAllWithPagination,
    onBulkItemsClickedAllUncheck, bulkDelete,{{ $hasBulkTrash ? ' bulkForceDelete, bulkRestore,' : '' }} confirmModal,
@else
    loadData, filter, resolveUrl,
@endif
    onSort, onPageChange, onPerPageChange,{{ $hasPublishedAt && $hasEdit ? ' onUpdateItem,' : '' }}
} = useAppListing(props);
@if($hasTrash || $hasBulkTrash)

const showDeleted = ref(false);

function onShowDeletedChange(value) {
    if (!value && orderBy.column === 'deleted_at') {
        orderBy.column = 'id';
        orderBy.direction = 'asc';
    }
@if($hasBulkDelete)
    onBulkItemsClickedAllUncheck();
@endif
    filter('trashed', value ? 1 : '');
}
@endif
</script>
