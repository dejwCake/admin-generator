<template>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <ListingHeader
                        :createUrl="createUrl"
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
                                <th class="bulk-checkbox">
                                    <BulkCheckboxHeader :isClickedAll="isClickedAll"
                                                        :onToggleAll="onBulkItemsClickedAllWithPagination"/>
                                </th>

                                <Sortable v-if="isColumnVisible(2)" :column="'id'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.id }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(4)" :column="'user_id'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.user_id }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(0)" :column="'title'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.title }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(0)" :column="'name'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.name }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(1)" :column="'first_name'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.first_name }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(0)" :column="'last_name'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.last_name }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(0)" :column="'subject'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.subject }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(1)" :column="'email'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.email }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(5)" :column="'language'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.language }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(6)" :column="'long_text'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.long_text }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(3)" :column="'published_at'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.published_at }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(7)" :column="'published_to'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.published_to }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(8)" :column="'date_start'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.date_start }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(9)" :column="'time_start'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.time_start }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'date_time_end'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.date_time_end }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'released_at'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.released_at }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'enabled'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.enabled }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'send'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.send }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'price'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.price }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'rating'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.rating }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'views'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.views }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'created_by_admin_user_id'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.created_by_admin_user_id }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'updated_by_admin_user_id'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.updated_by_admin_user_id }}
                                </Sortable>

                                <th></th>
                            </tr>
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
                            </thead>
                            <tbody>
                            <tr
                                v-for="(item, index) in collection"
                                :key="item.id"
                                :class="bulkItems[item.id] ? 'bg-bulk' : ''"
                            >
                                <td class="bulk-checkbox">
                                    <BulkCheckboxRow :itemId="item.id" :checked="!!bulkItems[item.id]"
                                                     :disabled="bulkCheckingAllLoader" :onToggle="onBulkItemClicked"/>
                                </td>

                                <td v-if="isColumnVisible(2)">{{ item.id }}</td>
                                <td v-if="isColumnVisible(4)">{{ item.user?.name }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.title }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.name }}</td>
                                <td v-if="isColumnVisible(1)">{{ item.first_name }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.last_name }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.subject }}</td>
                                <td v-if="isColumnVisible(1)">{{ item.email }}</td>
                                <td v-if="isColumnVisible(5)">{{ item.language }}</td>
                                <td v-if="isColumnVisible(6)">{{ item.long_text }}</td>
                                <td v-if="isColumnVisible(3)">{{ formatDate(item.published_at) }}</td>
                                <td v-if="isColumnVisible(7)">{{ formatDate(item.published_to) }}</td>
                                <td v-if="isColumnVisible(8)">{{ formatDate(item.date_start) }}</td>
                                <td v-if="isColumnVisible(9)">{{ formatTime(item.time_start) }}</td>
                                <td v-if="isColumnVisible(10)">{{ formatDatetime(item.date_time_end) }}</td>
                                <td v-if="isColumnVisible(10)">{{ formatDatetime(item.released_at) }}</td>
                                <td v-if="isColumnVisible(10)">
                                    <ToggleSwitch
                                        v-model="collection[index].enabled"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="enabled"
                                        :row="collection[index]"
                                    />
                                </td>
                                <td v-if="isColumnVisible(10)">
                                    <ToggleSwitch
                                        v-model="collection[index].send"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="send"
                                        :row="collection[index]"
                                    />
                                </td>
                                <td v-if="isColumnVisible(10)">{{ item.price }}</td>
                                <td v-if="isColumnVisible(10)">{{ item.rating }}</td>
                                <td v-if="isColumnVisible(10)">{{ item.views }}</td>
                                <td v-if="isColumnVisible(10)">{{ item.created_by_admin_user_id }}</td>
                                <td v-if="isColumnVisible(10)">{{ item.updated_by_admin_user_id }}</td>

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
import {formatDate, formatDatetime, formatTime} from '@craftable/utils/dateFormatters.js';
import Sortable from '@craftable/components/listing/Sortable.vue';
import Pagination from '@craftable/components/listing/Pagination.vue';
import Search from '@craftable/components/listing/Search.vue';
import PerPage from '@craftable/components/listing/PerPage.vue';
import EmptyState from '@craftable/components/listing/EmptyState.vue';
import BulkCheckboxHeader from '@craftable/components/listing/BulkCheckboxHeader.vue';
import BulkCheckboxRow from '@craftable/components/listing/BulkCheckboxRow.vue';
import BulkOperationsBar from '@craftable/components/listing/BulkOperationsBar.vue';
import ListingHeader from '@craftable/components/listing/ListingHeader.vue';
import EditButton from '@craftable/components/listing/EditButton.vue';
import DeleteButton from '@craftable/components/listing/DeleteButton.vue';
import ToggleSwitch from '@craftable/components/listing/ToggleSwitch.vue';
import ConfirmModal from '@craftable/components/ConfirmModal.vue';

const props = defineProps({
    url: {type: String, required: true},
    data: {type: Object, default: null},
    timezone: {type: String, default: 'UTC'},
    activation: {type: Boolean, required: true},
    translations: {type: Object, default: () => ({})},
    createUrl: {type: String, default: ''},
    editUrlTemplate: {type: String, required: true},
    updateUrlTemplate: {type: String, required: true},
    destroyUrlTemplate: {type: String, required: true},
    bulkAllUrl: {type: String, default: ''},
    bulkDestroyUrl: {type: String, default: ''},
    resendActivationUrlTemplate: {type: String, default: ''},
    impersonalLoginUrlTemplate: {type: String, default: ''},
    canImpersonalLogin: {type: Boolean, default: false},
});

const cardBody = ref(null);
const {isColumnVisible} = useResponsiveColumns(cardBody);

const {
    pagination, orderBy, filters, search, collection, now,
    bulkItems, bulkCheckingAllLoader,
    isClickedAll, clickedBulkItemsCount, loadData, filter, resolveUrl,
    onBulkItemClicked,
    onBulkItemsClickedAll, onBulkItemsClickedAllWithPagination,
    onBulkItemsClickedAllUncheck, bulkDelete, getAction, confirmModal,
    onSort, onPageChange, onPerPageChange,
} = useAppListing(props);
</script>
