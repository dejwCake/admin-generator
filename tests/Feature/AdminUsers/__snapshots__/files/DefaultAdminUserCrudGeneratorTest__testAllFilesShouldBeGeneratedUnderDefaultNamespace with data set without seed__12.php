@extends('brackets/admin-ui::admin.layout.default')

@section('title', __('admin.admin-user.actions.create'))

@section('body')

    <div class="container-xl">

        <div class="card">

            <admin-user-form
                :action="'{{ $action }}'"
                :activation="!!'{{ $activation }}'"
                inline-template>

                <form class="form-horizontal form-create" method="post" @submit.prevent="onSubmit" :action="action">

                    <div class="card-header">
                        <i class="fa fa-plus"></i> {{ __('admin.admin-user.actions.create') }}
                    </div>

                    <div class="card-body">

                        @include('admin.admin-user.components.form-elements')

                    </div>

                    <div class="card-footer">
	                    <button type="submit" class="btn btn-primary" :disabled="submiting">
		                    <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            {{ __('brackets/admin-ui::admin.btn.save') }}
	                    </button>
                    </div>

                </form>

            </admin-user-form>

        </div>

    </div>

@endsection
