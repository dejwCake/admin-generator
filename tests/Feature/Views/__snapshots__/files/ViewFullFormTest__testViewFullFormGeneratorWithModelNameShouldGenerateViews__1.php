@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_categ-ory.actions.edit', ['name' => $categOry->title]))

@section('body')

    <div class="container-xl">

        <div class="card">

            <billing-categ-ory-form
                :action="'{{ route('admin/billing-categ-ories/update', ['categOry' => $categOry]) }}'"
                :data="{{ $categOry->toJson() }}"
                v-cloak
                inline-template>

                <form class="form-horizontal" method="post" @submit.prevent="onSubmit" :action="action">

                    <div class="card-header">
                        <i class="fa fa-plus"></i> {{ trans('admin.billing_categ-ory.actions.edit', ['name' => $categOry->title]) }}
                    </div>

                    <div class="card-body">

                        <div class="form-group row align-items-center" :class="{'has-danger': errors.has('title'), 'has-success': fields.title && fields.title.valid }">
    <label for="title" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{ trans('admin.billing_categ-ory.columns.title') }}</label>
        <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <input type="text" v-model="form.title" v-validate="'required'" @input="validate($event)" class="form-control" :class="{'form-control-danger': errors.has('title'), 'form-control-success': fields.title && fields.title.valid}" id="title" name="title" placeholder="{{ trans('admin.billing_categ-ory.columns.title') }}">
        <div v-if="errors.has('title')" class="form-control-feedback form-text" v-cloak>@{{ errors.first('title') }}</div>
    </div>
</div>



                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            {{ trans('brackets/admin-ui::admin.btn.save') }}
                        </button>
                    </div>

                </form>

            </billing-categ-ory-form>

        </div>

    </div>

@endsection