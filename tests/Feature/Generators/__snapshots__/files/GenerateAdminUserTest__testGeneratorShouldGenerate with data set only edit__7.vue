<template>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <ListingHeader
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

                                <Sortable v-if="isColumnVisible(2)" :column="'id'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.id }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(1)" :column="'first_name'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.first_name }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(0)" :column="'last_name'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.last_name }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(1)" :column="'email'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.email }}
                                </Sortable>
                                <Sortable v-if="activation && isColumnVisible(3)" :column="'activated'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.activated }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(4)" :column="'forbidden'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.forbidden }}
                                </Sortable>
                                <Sortable v-if="isColumnVisible(5)" :column="'language'" :orderBy="orderBy" @sort="onSort">
                                    {{ translations.columns.language }}
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
                                <td v-if="isColumnVisible(1)">{{ item.first_name }}</td>
                                <td v-if="isColumnVisible(0)">{{ item.last_name }}</td>
                                <td v-if="isColumnVisible(1)">{{ item.email }}</td>
                                <td v-if="activation && isColumnVisible(3)">
                                    <ToggleSwitch
                                        v-model="collection[index].activated"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="activated"
                                        :row="collection[index]"
                                        variant="success"
                                    />
                                </td>
                                <td v-if="isColumnVisible(4)">
                                    <ToggleSwitch
                                        v-model="collection[index].forbidden"
                                        :url="resolveUrl(updateUrlTemplate, item)"
                                        column="forbidden"
                                        :row="collection[index]"
                                        variant="danger"
                                    />
                                </td>
                                <td v-if="isColumnVisible(5)">{{ item.language }}</td>

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
                        :translations="translations"
                    />
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import EditButton from '@craftable/components/listing/EditButton.vue';
import EmptyState from '@craftable/components/listing/EmptyState.vue';
import ListingHeader from '@craftable/components/listing/ListingHeader.vue';
import Pagination from '@craftable/components/listing/Pagination.vue';
import PerPage from '@craftable/components/listing/PerPage.vue';
import Search from '@craftable/components/listing/Search.vue';
import Sortable from '@craftable/components/listing/Sortable.vue';
import ToggleSwitch from '@craftable/components/listing/ToggleSwitch.vue';
import {ref} from 'vue';
import {useAppListing} from '../composables/useAppListing.js';
import {useResponsiveColumns} from '@craftable/composables/useResponsiveColumns.js';

const props = defineProps({
    url: {type: String, required: true},
    data: {type: Object, default: null},
    timezone: {type: String, default: 'UTC'},
    activation: {type: Boolean, required: true},
    translations: {type: Object, default: () => ({})},
    editUrlTemplate: {type: String, required: true},
    updateUrlTemplate: {type: String, required: true},
    resendActivationUrlTemplate: {type: String, default: ''},
    impersonalLoginUrlTemplate: {type: String, default: ''},
    canImpersonalLogin: {type: Boolean, default: false},
});

const cardBody = ref(null);
const {isColumnVisible} = useResponsiveColumns(cardBody);

const {
    pagination, orderBy, filters, search, collection, now,
    loadData, filter, resolveUrl, getAction,
    onSort, onPageChange, onPerPageChange,
} = useAppListing(props);
</script>
