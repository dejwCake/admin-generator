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

@foreach($columns as $column)
@if($column->name === 'activated')
                                <Sortable v-if="activation && isColumnVisible({{ $column->priority }})" :column="'{{ $column->name }}'" :orderBy="orderBy" @sort="onSort">
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

@foreach($columns as $column)
@if($column->name === 'activated')
                                <td v-if="activation && isColumnVisible({{ $column->priority }})">
                                    <ToggleSwitch
                                        v-model="collection[index].{{ $column->name }}"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="{{ $column->name }}"
                                        :row="collection[index]"
                                        variant="success"
                                    />
                                </td>
@elseif($column->name === 'forbidden')
                                <td v-if="isColumnVisible({{ $column->priority }})">
                                    <ToggleSwitch
                                        v-model="collection[index].{{ $column->name }}"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="{{ $column->name }}"
                                        :row="collection[index]"
                                        variant="danger"
                                    />
                                </td>
@elseif(in_array($column->majorType, ['bool'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">
                                    <ToggleSwitch
                                        v-model="collection[index].{{ $column->name }}"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="{{ $column->name }}"
                                        :row="collection[index]"
                                    />
                                </td>
@elseif(in_array($column->majorType, ['date'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} formatDate(item.{{ $column->name }}) }}</td>
@elseif(in_array($column->majorType, ['time'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} formatTime(item.{{ $column->name }}) }}</td>
@elseif(in_array($column->majorType, ['datetime'], true))
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} formatDatetime(item.{{ $column->name }}) }}</td>
@elseif($column->isForeignKey && $relations->hasBelongsToByColumn($column->name))
@php $belongsTo = $relations->getBelongsToByColumn($column->name); @endphp
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} item.{{ $belongsTo->relationMethodName }}?.{{ $belongsTo->relatedLabel }} }}</td>
@else
                                <td v-if="isColumnVisible({{ $column->priority }})">{{ '{{' }} item.{{ $column->name }} }}</td>
@endif
@endforeach

                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button
                                            v-if="canImpersonalLogin"
                                            class="btn btn-sm btn-success"
                                            v-show="item.activated"
                                            @click="getAction(resolveUrl(impersonalLoginUrlTemplate, item))"
                                            :title="translations.impersonal_login_btn"
                                            role="button"
                                        >
                                            <i class="fa fa-user"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm btn-warning"
                                            v-show="!item.activated"
                                            @click="getAction(resolveUrl(resendActivationUrlTemplate, item))"
                                            :title="translations.resend_activation_btn"
                                            role="button"
                                        >
                                            <i class="fa fa-envelope"></i>
                                        </button>
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

<script setup>
import {ref} from 'vue';
import {useAppListing} from '../composables/useAppListing.js';
import {useResponsiveColumns} from '@craftable/composables/useResponsiveColumns.js';
@if($hasDateColumns)
import {!! '{' . $dateImports->implode(', ') . '}' !!} from '@craftable/utils/dateFormatters.js';
@endif
import Sortable from '@craftable/components/listing/Sortable.vue';
import Pagination from '@craftable/components/listing/Pagination.vue';
import Search from '@craftable/components/listing/Search.vue';
import PerPage from '@craftable/components/listing/PerPage.vue';
import EmptyState from '@craftable/components/listing/EmptyState.vue';
@if($hasBulk)
import BulkCheckboxHeader from '@craftable/components/listing/BulkCheckboxHeader.vue';
import BulkCheckboxRow from '@craftable/components/listing/BulkCheckboxRow.vue';
import BulkOperationsBar from '@craftable/components/listing/BulkOperationsBar.vue';
@endif
import ListingHeader from '@craftable/components/listing/ListingHeader.vue';
import EditButton from '@craftable/components/listing/EditButton.vue';
import DeleteButton from '@craftable/components/listing/DeleteButton.vue';
@if($hasSwitchColumns)
import ToggleSwitch from '@craftable/components/listing/ToggleSwitch.vue';
@endif
import ConfirmModal from '@craftable/components/ConfirmModal.vue';

const props = defineProps({
    url: {type: String, required: true},
    data: {type: Object, default: null},
    timezone: {type: String, default: 'UTC'},
    activation: {type: Boolean, required: true},
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
    resendActivationUrlTemplate: {type: String, default: ''},
    impersonalLoginUrlTemplate: {type: String, default: ''},
    canImpersonalLogin: {type: Boolean, default: false},
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
    onBulkItemsClickedAllUncheck, bulkDelete, getAction, confirmModal,
@else
    loadData, filter, resolveUrl, getAction, confirmModal,
@endif
    onSort, onPageChange, onPerPageChange,
} = useAppListing(props);
</script>
