@php
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
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
                                        :onBulkDelete="() => bulkDelete(bulkDestroyUrl, {
                                            ...translations,
                                            confirm_title: translations.confirm_delete_title,
                                            confirm_text: translations.confirm_delete_text_multiple,
                                            confirm_btn: translations.delete_btn,
                                        })"
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
@if($hasEdit)
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
@if($hasEdit)
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
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} item.{{ $belongsTo->relationMethodName }}?.{{ $belongsTo->relatedLabel }} }}</td>
@else
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} item.{{ $column->name }} }}</td>
@endif
@endforeach

                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
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
@endif
@if($hasEdit)
    updateUrlTemplate: {type: String, required: true},
@endif
@if($hasDelete)
    destroyUrlTemplate: {type: String, required: true},
@endif
@if($hasBulkDelete)
    bulkAllUrl: {type: String, default: ''},
    bulkDestroyUrl: {type: String, default: ''},
@endif
});

const cardBody = ref(null);
// The numbers passed to isColumnVisible() are priorities, not column positions: a lower number
// survives a narrower card, columns sharing a number appear and disappear together, and anything
// above 3 is hidden once the card is under 1200px -- which an ordinary laptop already is. The
// generator can only guess, so tune them to this resource rather than leaving them in column order.
const {isColumnVisible} = useResponsiveColumns(cardBody);

const {
    pagination, orderBy, filters, search, collection, now,
@if($hasBulkDelete)
    bulkItems, bulkCheckingAllLoader,
    isClickedAll, clickedBulkItemsCount, loadData, filter, resolveUrl,
    onBulkItemClicked,
    onBulkItemsClickedAll, onBulkItemsClickedAllWithPagination,
    onBulkItemsClickedAllUncheck, bulkDelete, confirmModal,
@else
    loadData, filter, resolveUrl,
@endif
    onSort, onPageChange, onPerPageChange,{{ $hasPublishedAt && $hasEdit ? ' onUpdateItem,' : '' }}
} = useAppListing(props);
</script>
