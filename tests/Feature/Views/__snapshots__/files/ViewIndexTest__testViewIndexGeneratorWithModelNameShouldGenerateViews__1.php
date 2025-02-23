@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_categ-ory.actions.index'))

@section('body')

    <billing-categ-ory-listing
        :data="{{ $data->toJson() }}"
        :url="'{{ $url }}'"
        :trans="{{ json_encode(trans('brackets/admin-ui::admin.dialogs')) }}"
        inline-template>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> {{ trans('admin.billing_categ-ory.actions.index') }}
                        <a class="btn btn-primary btn-spinner btn-sm pull-right m-b-0" href="{{ $createUrl }}" role="button"><i class="fa fa-plus"></i>&nbsp; {{ trans('admin.billing_categ-ory.actions.create') }}</a>
                    </div>
                    <div class="card-body" v-cloak>
                        <div class="card-block">
                            <form @submit.prevent="">
                                <div class="row justify-content-md-between">
                                    <div class="col col-lg-7 col-xl-5 form-group">
                                        <div class="input-group">
                                            <input class="form-control" placeholder="{{ trans('brackets/admin-ui::admin.placeholder.search') }}" v-model="search" @keyup.enter="filter('search', $event.target.value)" />
                                            <span class="input-group-append">
                                                <button type="button" class="btn btn-primary" @click="filter('search', search)"><i class="fa fa-search"></i>&nbsp; {{ trans('brackets/admin-ui::admin.btn.search') }}</button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-auto form-group ">
                                        <select class="form-control" v-model="pagination.state.per_page">
                                            
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </form>

                            <table class="table table-hover table-listing">
                                <thead>
                                    <tr>
                                        <th class="bulk-checkbox">
                                            <input class="form-check-input" id="enabled" type="checkbox" v-model="isClickedAll" v-validate="''" data-vv-name="enabled"  name="enabled_fake_element" @click="onBulkItemsClickedAllWithPagination()">
                                            <label class="form-check-label" for="enabled">
                                                #
                                            </label>
                                        </th>

                                        <th is='sortable' :column="'id'">{{ trans('admin.billing_categ-ory.columns.id') }}</th>
                                        <th is='sortable' :column="'user_id'">{{ trans('admin.billing_categ-ory.columns.user_id') }}</th>
                                        <th is='sortable' :column="'title'">{{ trans('admin.billing_categ-ory.columns.title') }}</th>
                                        <th is='sortable' class="text-center" :column="'published_at'">{{ trans('admin.billing_categ-ory.columns.published_at') }}</th>
                                        <th is='sortable' :column="'date_start'">{{ trans('admin.billing_categ-ory.columns.date_start') }}</th>
                                        <th is='sortable' :column="'time_start'">{{ trans('admin.billing_categ-ory.columns.time_start') }}</th>
                                        <th is='sortable' :column="'date_time_end'">{{ trans('admin.billing_categ-ory.columns.date_time_end') }}</th>
                                        <th is='sortable' :column="'description'">{{ trans('admin.billing_categ-ory.columns.description') }}</th>
                                        <th is='sortable' :column="'enabled'">{{ trans('admin.billing_categ-ory.columns.enabled') }}</th>
                                        <th is='sortable' :column="'send'">{{ trans('admin.billing_categ-ory.columns.send') }}</th>
                                        <th is='sortable' :column="'price'">{{ trans('admin.billing_categ-ory.columns.price') }}</th>
                                        <th is='sortable' :column="'views'">{{ trans('admin.billing_categ-ory.columns.views') }}</th>
                                        <th is='sortable' :column="'created_by_admin_user_id'">{{ trans('admin.billing_categ-ory.columns.created_by_admin_user_id') }}</th>
                                        <th is='sortable' :column="'updated_by_admin_user_id'">{{ trans('admin.billing_categ-ory.columns.updated_by_admin_user_id') }}</th>

                                        <th></th>
                                    </tr>
                                    <tr v-show="(clickedBulkItemsCount > 0) || isClickedAll">
                                        <td class="bg-bulk-info d-table-cell text-center" colspan="16">
                                            <span class="align-middle font-weight-light text-dark">{{ trans('brackets/admin-ui::admin.listing.selected_items') }} @{{ clickedBulkItemsCount }}.  <a href="#" class="text-primary" @click="onBulkItemsClickedAll('/admin/billing-categ-ories')" v-if="(clickedBulkItemsCount < pagination.state.total)"> <i class="fa" :class="bulkCheckingAllLoader ? 'fa-spinner' : ''"></i> {{ trans('brackets/admin-ui::admin.listing.check_all_items') }} @{{ pagination.state.total }}</a> <span class="text-primary">|</span> <a
                                                        href="#" class="text-primary" @click="onBulkItemsClickedAllUncheck()">{{ trans('brackets/admin-ui::admin.listing.uncheck_all_items') }}</a>  </span>

                                            <span class="pull-right pr-2">
                                                <button class="btn btn-sm btn-danger pr-3 pl-3" @click="bulkDelete('/admin/billing-categ-ories/bulk-destroy')">{{ trans('brackets/admin-ui::admin.btn.delete') }}</button>
                                            </span>

                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in collection" :key="item.id" :class="bulkItems[item.id] ? 'bg-bulk' : ''">
                                        <td class="bulk-checkbox">
                                            <input class="form-check-input" :id="'enabled' + item.id" type="checkbox" v-model="bulkItems[item.id]" v-validate="''" :data-vv-name="'enabled' + item.id"  :name="'enabled' + item.id + '_fake_element'" @click="onBulkItemClicked(item.id)" :disabled="bulkCheckingAllLoader">
                                            <label class="form-check-label" :for="'enabled' + item.id">
                                            </label>
                                        </td>

                                        <td>@{{ item.id }}</td>
                                        <td>@{{ item.user_id }}</td>
                                        <td>@{{ item.title }}</td>
                                        <td class="text-center text-nowrap">
                                            <span v-if="item.published_at <= now">
                                                @{{ item.published_at | datetime('DD.MM.YYYY, HH:mm') }}
                                            </span>
                                                <span v-if="item.published_at > now">
                                                <small>{{ trans('admin.billing_categ-ory.actions.will_be_published') }}</small><br />
                                                @{{ item.published_at | datetime('DD.MM.YYYY, HH:mm') }}
                                                <span class="cursor-pointer" @click="publishLater(item.resource_url, collection[index], 'publishLaterDialog')" title="{{ trans('brackets/admin-ui::admin.operation.publish_later') }}" role="button"><i class="fa fa-calendar"></i></span>
                                            </span>
                                            <div v-if="!item.published_at">
                                                <span class="btn btn-sm btn-info text-white mb-1" @click="publishLater(item.resource_url, collection[index], 'publishLaterDialog')" title="{{ trans('brackets/admin-ui::admin.operation.publish_later') }}" role="button"><i class="fa fa-calendar"></i>&nbsp;&nbsp;{{ trans('brackets/admin-ui::admin.operation.publish_later') }}</span>
                                            </div>
                                            <div v-if="!item.published_at || item.published_at > now">
                                                <form class="d-inline" @submit.prevent="publishNow(item.resource_url, collection[index], 'publishNowDialog')">
                                                    <button type="submit" class="btn btn-sm btn-success text-white" title="{{ trans('brackets/admin-ui::admin.operation.publish_now') }}"><i class="fa fa-send"></i>&nbsp;&nbsp;{{ trans('brackets/admin-ui::admin.operation.publish_now') }}</button>
                                                </form>
                                            </div>
                                            <div v-if="item.published_at && item.published_at < now">
                                                <form class="d-inline" @submit.prevent="unpublishNow(item.resource_url, collection[index])">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ trans('brackets/admin-ui::admin.operation.unpublish_now') }}"><i class="fa fa-send"></i>&nbsp;&nbsp;{{ trans('brackets/admin-ui::admin.operation.unpublish_now') }}</button>
                                                </form>
                                            </div>
                                        </td>

                                        <td>@{{ item.date_start | date }}</td>
                                        <td>@{{ item.time_start | time }}</td>
                                        <td>@{{ item.date_time_end | datetime }}</td>
                                        <td>@{{ item.description }}</td>
                                        <td>
                                            <label class="switch switch-3d switch-success">
                                                <input type="checkbox" class="switch-input" v-model="collection[index].enabled" @change="toggleSwitch(item.resource_url, 'enabled', collection[index])">
                                                <span class="switch-slider"></span>
                                            </label>
                                        </td>

                                        <td>@{{ item.send }}</td>
                                        <td>@{{ item.price }}</td>
                                        <td>@{{ item.views }}</td>
                                        <div class="user-detail-tooltips-list">
                                            <td>
                                                <user-detail-tooltip :user="item.created_by_admin_user" v-if="item.created_by_admin_user">
                                                    <p>Created on @{{ item.created_at | datetime('HH:mm:ss, DD.MM.YYYY') }}</p>
                                                </user-detail-tooltip>
                                            </td>
                                        </div>

                                        <div class="user-detail-tooltips-list">
                                            <td>
                                                <user-detail-tooltip :user="item.updated_by_admin_user" v-if="item.updated_by_admin_user">
                                                    <p>Updated on @{{ item.updated_at | datetime('HH:mm:ss, DD.MM.YYYY') }}</p>
                                                </user-detail-tooltip>
                                            </td>
                                        </div>


                                        <td>
                                            <div class="row no-gutters">
                                                <div class="col-auto">
                                                    <a class="btn btn-sm btn-spinner btn-info" :href="item.resource_url + '/edit'" title="{{ trans('brackets/admin-ui::admin.btn.edit') }}" role="button"><i class="fa fa-edit"></i></a>
                                                </div>
                                                <form class="col" @submit.prevent="deleteItem(item.resource_url)">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ trans('brackets/admin-ui::admin.btn.delete') }}"><i class="fa fa-trash-o"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row" v-if="pagination.state.total > 0">
                                <div class="col-sm">
                                    <span class="pagination-caption">{{ trans('brackets/admin-ui::admin.pagination.overview') }}</span>
                                </div>
                                <div class="col-sm-auto">
                                    <pagination></pagination>
                                </div>
                            </div>

                            <div class="no-items-found" v-if="!collection.length > 0">
                                <i class="icon-magnifier"></i>
                                <h3>{{ trans('brackets/admin-ui::admin.index.no_items') }}</h3>
                                <p>{{ trans('brackets/admin-ui::admin.index.try_changing_items') }}</p>
                                <a class="btn btn-primary btn-spinner" href="{{ $createUrl }}" role="button"><i class="fa fa-plus"></i>&nbsp; {{ trans('admin.billing_categ-ory.actions.create') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </billing-categ-ory-listing>

@endsection