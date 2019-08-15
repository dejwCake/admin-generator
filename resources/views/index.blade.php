{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.index'))

{{'@'}}section('body')

    <{{ $modelJSName }}-listing
        :data="{{'{{'}} $data->toJson() }}"
        :url="'{{'{{'}} url('admin/{{ $resource }}') }}'"
@if($containsPublishedAtColumn)
        :trans="{{'{{'}} json_encode(trans('brackets/admin-ui::admin.dialogs')) }}"
@endif
        inline-template>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> {{'{{'}} trans('admin.{{ $modelLangFormat }}.actions.index') }}
@if($export)
                        <a class="btn btn-primary btn-sm pull-right m-b-0 ml-2" href="{{'{{'}} url('admin/{{ $resource }}/export') }}" role="button"><i class="fa fa-file-excel-o"></i>&nbsp; {{'{{'}} trans('admin.{{ $modelLangFormat }}.actions.export') }}</a>
@endif
                        <a class="btn btn-primary btn-spinner btn-sm pull-right m-b-0" href="{{'{{'}} url('admin/{{ $resource }}/create') }}" role="button"><i class="fa fa-plus"></i>&nbsp; {{'{{'}} trans('admin.{{ $modelLangFormat }}.actions.create') }}</a>
                    </div>
                    <div class="card-body" v-cloak>
                        <form @submit.prevent="">
                            <div class="row justify-content-md-between">
                                <div class="col col-lg-7 col-xl-5 form-group">
                                    <div class="input-group">
                                        <input class="form-control" placeholder="@{{ trans('brackets/admin-ui::admin.placeholder.search') }}" v-model="search" @keyup.enter="filter('search', $event.target.value)" />
                                        <span class="input-group-append">
                                            <button type="button" class="btn btn-primary" @click="filter('search', search)"><i class="fa fa-search"></i>&nbsp; @{{ trans('brackets/admin-ui::admin.btn.search') }}</button>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-sm-auto form-group ">
                                    <select class="form-control" v-model="pagination.state.per_page">
                                        {{-- TODO extract these options into a config or smtn --}}
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
                                    @foreach($columns as $col)
@if($col['name'] === 'published_at')<th is='sortable' class="text-center" :column="'{{ $col['name'] }}'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</th>
@else<th is='sortable' :column="'{{ $col['name'] }}'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</th>
@endif
                                    @endforeach

                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in collection">
                                    @foreach($columns as $col)@if($col['switch'])<td>
                                        <label class="switch switch-3d switch-success">
                                            <input type="checkbox" class="switch-input" v-model="collection[index].{{ $col['name'] }}" @change="toggleSwitch(item.resource_url, '{{ $col['name'] }}', collection[index])">
                                            <span class="switch-slider"></span>
                                        </label>
                                    </td>
@elseif($col['name'] === 'created_by_admin_user_id')<div class="user-detail-tooltips-list">
                                        <td>
                                            <user-detail-tooltip :user="item.created_by_admin_user" v-if="item.created_by_admin_user">
                                                <p>Created on {{'@{{'}} item.created_at | datetime('HH:mm:ss, DD.MM.YYYY') }}</p>
                                            </user-detail-tooltip>
                                        </td>
                                    </div>
@elseif($col['name'] === 'updated_by_admin_user_id')<div class="user-detail-tooltips-list">
                                        <td>
                                            <user-detail-tooltip :user="item.updated_by_admin_user" v-if="item.updated_by_admin_user">
                                                <p>Updated on {{'@{{'}} item.updated_at | datetime('HH:mm:ss, DD.MM.YYYY') }}</p>
                                            </user-detail-tooltip>
                                        </td>
                                    </div>
@elseif($col['name'] === 'published_at')<td class="text-center text-nowrap">
                                        <span v-if="item.published_at <= now">
                                            {{'@{{'}} item.published_at | datetime('DD.MM.YYYY, HH:mm') }}
                                        </span>
                                            <span v-if="item.published_at > now">
                                            <small>{{'{{'}} trans('admin.{{ $modelLangFormat }}.actions.will_be_published') }}</small><br />
                                            {{'@{{'}} item.published_at | datetime('DD.MM.YYYY, HH:mm') }}
                                            <span class="cursor-pointer" @click="publishLater(item.resource_url, collection[index], 'publishLaterDialog')" title="@{{ trans('brackets/admin-ui::admin.operation.publish_later') }}" role="button"><i class="fa fa-calendar"></i></span>
                                        </span>
                                        <div v-if="!item.published_at">
                                            <span class="btn btn-sm btn-info" @click="publishLater(item.resource_url, collection[index], 'publishLaterDialog')" title="@{{ trans('brackets/admin-ui::admin.operation.publish_later') }}" role="button"><i class="fa fa-calendar"></i>&nbsp;&nbsp;@{{ trans('brackets/admin-ui::admin.operation.publish_later') }}</span>
                                        </div>
                                        <div v-if="!item.published_at || item.published_at > now">
                                            <form class="d-inline" @submit.prevent="publishNow(item.resource_url, collection[index], 'publishNowDialog')">
                                                <button type="submit" class="btn btn-sm btn-success text-white" title="@{{ trans('brackets/admin-ui::admin.operation.publish_now') }}"><i class="fa fa-send"></i>&nbsp;&nbsp;@{{ trans('brackets/admin-ui::admin.operation.publish_now') }}</button>
                                            </form>
                                        </div>
                                        <div v-if="item.published_at && item.published_at < now">
                                            <form class="d-inline" @submit.prevent="unpublishNow(item.resource_url, collection[index])">
                                                <button type="submit" class="btn btn-sm btn-danger" title="@{{ trans('brackets/admin-ui::admin.operation.unpublish_now') }}"><i class="fa fa-send"></i>&nbsp;&nbsp;@{{ trans('brackets/admin-ui::admin.operation.unpublish_now') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                    @else<td>{{'@{{'}} item.{{ $col['name'] }}{{ $col['filters'] }} }}</td>@endif

                                    @endforeach

                                    <td>
                                        <div class="row no-gutters">
                                            <div class="col-auto">
                                                <a class="btn btn-sm btn-spinner btn-info" :href="item.resource_url + '/edit'" title="@{{ trans('brackets/admin-ui::admin.btn.edit') }}" role="button"><i class="fa fa-edit"></i></a>
                                            </div>
                                            <form class="col" @submit.prevent="deleteItem(item.resource_url)">
                                                <button type="submit" class="btn btn-sm btn-danger" title="@{{ trans('brackets/admin-ui::admin.btn.delete') }}"><i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="row" v-if="pagination.state.total > 0">
                            <div class="col-sm">
                                <span class="pagination-caption">@{{ trans('brackets/admin-ui::admin.pagination.overview') }}</span>
                            </div>
                            <div class="col-sm-auto">
                                <pagination></pagination>
                            </div>
                        </div>

	                    <div class="no-items-found" v-if="!collection.length > 0">
		                    <i class="icon-magnifier"></i>
		                    <h3>@{{ trans('brackets/admin-ui::admin.index.no_items') }}</h3>
		                    <p>@{{ trans('brackets/admin-ui::admin.index.try_changing_items') }}</p>
                            <a class="btn btn-primary btn-spinner" href="{{'{{'}} url('admin/{{ $resource }}/create') }}" role="button"><i class="fa fa-plus"></i>&nbsp; {{'{{'}} trans('admin.{{ $modelLangFormat }}.actions.create') }}</a>
	                    </div>
                    </div>
                </div>
            </div>
        </div>
    </{{ $modelJSName }}-listing>

{{'@'}}endsection