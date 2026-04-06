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
                        :createUrl="createUrl"
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
@if($hasBulk)
                                <th class="bulk-checkbox">
                                    <BulkCheckboxHeader :isClickedAll="isClickedAll"
                                                        :onToggleAll="onBulkItemsClickedAllWithPagination"/>
                                </th>
@endif

@foreach($columns as $col)
@if($col['name'] === 'published_at')
                                <Sortable v-if="isColumnVisible({{ $col['priority'] }})" :column="'{{ $col['name'] }}'" class="text-center" :orderBy="orderBy" @sort="onSort">
                                    {{ '{{' }} translations.columns.{{ $col['name'] }} }}
                                </Sortable>
@else
                                <Sortable v-if="isColumnVisible({{ $col['priority'] }})" :column="'{{ $col['name'] }}'" :orderBy="orderBy" @sort="onSort">
                                    {{ '{{' }} translations.columns.{{ $col['name'] }} }}
                                </Sortable>
@endif
@endforeach

                                <th></th>
                            </tr>
@if($hasBulk)
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
@if($hasBulk)
                                :class="bulkItems[item.id] ? 'bg-bulk' : ''"
@endif
                            >
@if($hasBulk)
                                <td class="bulk-checkbox">
                                    <BulkCheckboxRow :itemId="item.id" :checked="!!bulkItems[item.id]"
                                                     :disabled="bulkCheckingAllLoader" :onToggle="onBulkItemClicked"/>
                                </td>
@endif

@foreach($columns as $col)
@if($col['name'] === 'published_at')
                                <td v-if="isColumnVisible({{ $col['priority'] }})" class="text-center text-nowrap" style="position: relative;">
                                    <PublishedAtColumn
                                        :item="item"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        :now="now"
                                        :translations="translations"
                                        @update:item="onUpdateItem"
                                    />
                                </td>
@elseif($col['name'] === 'created_by_admin_user_id')
                                <td v-if="isColumnVisible({{ $col['priority'] }})">
                                    <UserDetailTooltip
                                        v-if="item.created_by_admin_user"
                                        :user="item.created_by_admin_user"
                                        :datetime-text="translations.created_on + ' ' + formatDatetime(item.created_at)"
                                    />
                                </td>
@elseif($col['name'] === 'updated_by_admin_user_id')
                                <td v-if="isColumnVisible({{ $col['priority'] }})">
                                    <UserDetailTooltip
                                        v-if="item.updated_by_admin_user"
                                        :user="item.updated_by_admin_user"
                                        :datetime-text="translations.updated_on + ' ' + formatDatetime(item.updated_at)"
                                    />
                                </td>
@elseif(in_array($col['majorType'], ['bool']))
                                <td v-if="isColumnVisible({{ $col['priority'] }})">
                                    <ToggleSwitch
                                        v-model="collection[index].{{ $col['name'] }}"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="{{ $col['name'] }}"
                                        :row="collection[index]"
                                    />
                                </td>
@elseif(in_array($col['majorType'], ['date']))
                                <td v-if="isColumnVisible({{ $col['priority'] }})">{{ '{{' }} formatDate(item.{{ $col['name'] }}) }}</td>
@elseif(in_array($col['majorType'], ['time']))
                                <td v-if="isColumnVisible({{ $col['priority'] }})">{{ '{{' }} formatTime(item.{{ $col['name'] }}) }}</td>
@elseif(in_array($col['majorType'], ['datetime']))
                                <td v-if="isColumnVisible({{ $col['priority'] }})">{{ '{{' }} formatDatetime(item.{{ $col['name'] }}) }}</td>
@elseif(isset($col['isForeignKey']) && $col['isForeignKey'] && $relations->hasBelongsToByColumn($col['name']))
@php $belongsTo = $relations->getBelongsToByColumn($col['name']); @endphp
                                <td v-if="isColumnVisible({{ $col['priority'] }})">{{ '{{' }} item.{{ $belongsTo->relationMethodName }}?.{{ $belongsTo->relatedLabel }} }}</td>
@else
                                <td v-if="isColumnVisible({{ $col['priority'] }})">{{ '{{' }} item.{{ $col['name'] }} }}</td>
@endif
@endforeach

                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <EditButton
                                            :url="resolveUrl(editUrlTemplate, item)"
                                            :translations="translations"
                                        />
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
                        :createUrl="createUrl"
                        :translations="translations"
                    />
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="confirmModal.show"
            :translations="confirmModal.translations"
            @confirm="confirmModal.onConfirm"
            @cancel="confirmModal.show = false"
        />
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
        $imports->push('{' . $dateImports . "} from '@craftable/utils/dateFormatters.js'");
    }
    $imports->push("Sortable from '@craftable/components/listing/Sortable.vue'");
    $imports->push("Pagination from '@craftable/components/listing/Pagination.vue'");
    $imports->push("Search from '@craftable/components/listing/Search.vue'");
    $imports->push("PerPage from '@craftable/components/listing/PerPage.vue'");
    $imports->push("EmptyState from '@craftable/components/listing/EmptyState.vue'");
    if ($hasBulk) {
        $imports->push("BulkCheckboxHeader from '@craftable/components/listing/BulkCheckboxHeader.vue'");
        $imports->push("BulkCheckboxRow from '@craftable/components/listing/BulkCheckboxRow.vue'");
        $imports->push("BulkOperationsBar from '@craftable/components/listing/BulkOperationsBar.vue'");
    }
    if ($hasPublishedAt) {
        $imports->push("PublishedAtColumn from '@craftable/components/listing/PublishedAtColumn.vue'");
    }
    $imports->push("ListingHeader from '@craftable/components/listing/ListingHeader.vue'");
    $imports->push("EditButton from '@craftable/components/listing/EditButton.vue'");
    $imports->push("DeleteButton from '@craftable/components/listing/DeleteButton.vue'");
    if ($hasUserDetailTooltip) {
        $imports->push("UserDetailTooltip from '@craftable/components/UserDetailTooltip.vue'");
    }
    if ($hasSwitchColumns) {
        $imports->push("ToggleSwitch from '@craftable/components/listing/ToggleSwitch.vue'");
    }
    $imports->push("ConfirmModal from '@craftable/components/ConfirmModal.vue'");
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
    createUrl: {type: String, default: ''},
@if($hasExport)
    exportUrl: {type: String, default: ''},
@endif
    editUrlTemplate: {type: String, required: true},
    updateUrlTemplate: {type: String, required: true},
    destroyUrlTemplate: {type: String, required: true},
@if($hasBulk)
    bulkAllUrl: {type: String, default: ''},
    bulkDestroyUrl: {type: String, default: ''},
@endif
});

const cardBody = ref(null);
const {isColumnVisible} = useResponsiveColumns(cardBody);

const {
    pagination, orderBy, filters, search, collection, now,
@if($hasBulk)
    bulkItems, bulkCheckingAllLoader,
    isClickedAll, clickedBulkItemsCount, loadData, filter, resolveUrl,
    onBulkItemClicked,
    onBulkItemsClickedAll, onBulkItemsClickedAllWithPagination,
    onBulkItemsClickedAllUncheck, bulkDelete, confirmModal,
@else
    loadData, filter, resolveUrl, confirmModal,
@endif
    onSort, onPageChange, onPerPageChange,{{ $hasPublishedAt ? ' onUpdateItem,' : '' }}
} = useAppListing(props);
</script>
