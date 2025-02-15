@extends('brackets/admin-ui::admin.layout.default')

@section('title', __('admin.admin-user.actions.edit', ['name' => $adminUser->first_name]))

@section('body')

    <div class="container-xl">

        <div class="card">

            <admin-user-form
                :action="'{{ $action }}'"
                :data="{{ $adminUser->toJson() }}"
                :activation="!!'{{ $activation }}'"
                inline-template>

                <form class="form-horizontal form-edit" method="post" @submit.prevent="onSubmit" :action="action">

                    <div class="card-header">
                        <i class="fa fa-pencil"></i> {{ __('admin.admin-user.actions.edit', ['name' => $adminUser->first_name]) }}
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
