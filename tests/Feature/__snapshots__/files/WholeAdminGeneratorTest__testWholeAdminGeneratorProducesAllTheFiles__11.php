@extends('brackets/admin-ui::admin.layout.default')

@section('title', __('admin.category.actions.create'))

@section('body')

    <div class="container-xl">

        <div class="card">

            <category-form
                :action="'{{ $action }}'"
                v-cloak
                inline-template>

                <form class="form-horizontal form-create" method="post" @submit.prevent="onSubmit" :action="action" novalidate>

                    <div class="card-header">
                        <i class="fa fa-plus"></i> {{ __('admin.category.actions.create') }}
                    </div>

                    <div class="card-body">
                        @include('admin.category.components.form-elements')
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" :disabled="submiting">
                            <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            {{ __('brackets/admin-ui::admin.btn.save') }}
                        </button>
                    </div>

                </form>

            </category-form>

        </div>

    </div>


@endsection