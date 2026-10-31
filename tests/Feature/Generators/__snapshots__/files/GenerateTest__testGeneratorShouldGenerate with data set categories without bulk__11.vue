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
                        <div class="col-auto mb-3 d-flex align-items-center me-auto">
                            <ShowDeleted
                                v-model="showDeleted"
                                :translations="translations"
                                @update:modelValue="onShowDeletedChange"
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

                                <Sortable v-if="isColumnVisible(2)" :column="'id'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.id }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(10)" :column="'uuid'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.uuid }}
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
                                <Sortable v-if="isColumnVisible(3)" :column="'published_at'" class="text-center" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.published_at }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(6)" :column="'published_to'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.published_to }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(7)" :column="'date_start'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.date_start }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(8)" :column="'time_start'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.time_start }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(9)" :column="'date_time_end'" :orderBy="orderBy" @sort="onSort">
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
                                <Sortable v-if="showDeleted && isColumnVisible(0)" :column="'deleted_at'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.deleted_at }}
                                </Sortable>

                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="(item, index) in collection"
                                :key="item.id"
                            >

                                <td v-if="isColumnVisible(2)">{{ item.id }}</td>
                                <td v-if="isColumnVisible(10)">{{ item.uuid }}</td>
                                <td v-if="isColumnVisible(4)">{{ item.user?.name }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.title }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.name }}</td>
                                <td v-if="isColumnVisible(1)">{{ item.first_name }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.last_name }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.subject }}</td>
                                <td v-if="isColumnVisible(1)">{{ item.email }}</td>
                                <td v-if="isColumnVisible(5)">{{ item.language }}</td>
                                <td v-if="isColumnVisible(3)" class="text-center text-nowrap" style="position: relative;">
                                    <template v-if="showDeleted">{{ formatDatetime(item.published_at) }}</template>
                                    <PublishedAtColumn
                                        v-else
                                        :item="item"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        :now="now"
                                        :translations="translations"
                                        @update:item="onUpdateItem"
                                    />
                                </td>
                                <td v-if="isColumnVisible(6)">{{ formatDate(item.published_to) }}</td>
                                <td v-if="isColumnVisible(7)">{{ formatDate(item.date_start) }}</td>
                                <td v-if="isColumnVisible(8)">{{ formatTime(item.time_start) }}</td>
                                <td v-if="isColumnVisible(9)">{{ formatDatetime(item.date_time_end) }}</td>
                                <td v-if="isColumnVisible(10)">{{ formatDatetime(item.released_at) }}</td>
                                <td v-if="isColumnVisible(10)">
                                    <ReadOnlySwitch v-if="showDeleted" :modelValue="item.enabled"/>
                                    <ToggleSwitch
                                        v-else
                                        v-model="collection[index].enabled"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="enabled"
                                        :row="collection[index]"
                                    />
                                </td>
                                <td v-if="isColumnVisible(10)">
                                    <ReadOnlySwitch v-if="showDeleted" :modelValue="item.send"/>
                                    <ToggleSwitch
                                        v-else
                                        v-model="collection[index].send"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="send"
                                        :row="collection[index]"
                                    />
                                </td>
                                <td v-if="isColumnVisible(10)">{{ item.price }}</td>
                                <td v-if="isColumnVisible(10)">{{ item.rating }}</td>
                                <td v-if="isColumnVisible(10)">{{ item.views }}</td>
                                <td v-if="isColumnVisible(10)">
                                    <UserDetailTooltip
                                        v-if="item.created_by_admin_user"
                                        :user="item.created_by_admin_user"
                                        :datetime-text="translations.created_on + ' ' + formatDatetime(item.created_at)"
                                    />
                                </td>
                                <td v-if="isColumnVisible(10)">
                                    <UserDetailTooltip
                                        v-if="item.updated_by_admin_user"
                                        :user="item.updated_by_admin_user"
                                        :datetime-text="translations.updated_on + ' ' + formatDatetime(item.updated_at)"
                                    />
                                </td>
                                <td v-if="showDeleted && isColumnVisible(0)">{{ formatDatetime(item.deleted_at) }}</td>

                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
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
                                        <template v-else>
                                            <ShowButton
                                                :url="resolveUrl(showUrlTemplate, item)"
                                                :translations="translations"
                                            />
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
                                        </template>
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

    </div>
</template>

<script setup>
import DeleteButton from '@craftable/components/listing/DeleteButton.vue';
import EditButton from '@craftable/components/listing/EditButton.vue';
import EmptyState from '@craftable/components/listing/EmptyState.vue';
import ForceDeleteButton from '@craftable/components/listing/ForceDeleteButton.vue';
import ListingHeader from '@craftable/components/listing/ListingHeader.vue';
import Pagination from '@craftable/components/listing/Pagination.vue';
import PerPage from '@craftable/components/listing/PerPage.vue';
import PublishedAtColumn from '@craftable/components/listing/PublishedAtColumn.vue';
import ReadOnlySwitch from '@craftable/components/ReadOnlySwitch.vue';
import RestoreButton from '@craftable/components/listing/RestoreButton.vue';
import Search from '@craftable/components/listing/Search.vue';
import ShowButton from '@craftable/components/listing/ShowButton.vue';
import ShowDeleted from '@craftable/components/listing/ShowDeleted.vue';
import Sortable from '@craftable/components/listing/Sortable.vue';
import ToggleSwitch from '@craftable/components/listing/ToggleSwitch.vue';
import UserDetailTooltip from '@craftable/components/UserDetailTooltip.vue';
import {formatDate, formatDatetime, formatTime} from '@craftable/utils/dateFormatters.js';
import {ref} from 'vue';
import {useAppListing} from '../composables/useAppListing.js';
import {useResponsiveColumns} from '@craftable/composables/useResponsiveColumns.js';

const props = defineProps({
    url: {type: String, required: true},
    data: {type: Object, default: null},
    timezone: {type: String, default: 'UTC'},
    translations: {type: Object, default: () => ({})},
    createUrl: {type: String, default: ''},
    showUrlTemplate: {type: String, required: true},
    editUrlTemplate: {type: String, required: true},
    updateUrlTemplate: {type: String, required: true},
    destroyUrlTemplate: {type: String, required: true},
    forceDestroyUrlTemplate: {type: String, required: true},
    restoreUrlTemplate: {type: String, required: true},
});

const cardBody = ref(null);
const {isColumnVisible} = useResponsiveColumns(cardBody);

const {
    pagination, orderBy, filters, search, collection, now,
    loadData, filter, resolveUrl,
    onSort, onPageChange, onPerPageChange, onUpdateItem,
} = useAppListing(props);

const showDeleted = ref(false);

function onShowDeletedChange(value) {
    if (!value && orderBy.column === 'deleted_at') {
        orderBy.column = 'id';
        orderBy.direction = 'asc';
    }
    filter('trashed', value ? 1 : '');
}
</script>
