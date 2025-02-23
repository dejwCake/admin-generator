@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_categ-ory.actions.edit', ['name' => $categOry->title]))

@section('body')

    <div class="container-xl">
        <div class="card">

            <billing-categ-ory-form
                :action="'{{ $action }}'"
                :data="{{ $categOry->toJson() }}"
                v-cloak
                inline-template>

                <form class="form-horizontal form-edit" method="post" @submit.prevent="onSubmit" :action="action" novalidate>


                    <div class="card-header">
                        <i class="fa fa-pencil"></i> {{ trans('admin.billing_categ-ory.actions.edit', ['name' => $categOry->title]) }}
                    </div>

                    <div class="card-body">
                        @include('admin.billing.categ-ory.components.form-elements')
                    </div>


                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" :disabled="submiting">
                            <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            {{ trans('brackets/admin-ui::admin.btn.save') }}
                        </button>
                    </div>

                </form>

            </billing-categ-ory-form>

        </div>
    </div>

@endsection