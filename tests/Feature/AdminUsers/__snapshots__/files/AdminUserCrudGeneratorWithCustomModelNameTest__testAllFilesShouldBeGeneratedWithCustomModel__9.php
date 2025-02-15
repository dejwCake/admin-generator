@extends('brackets/admin-ui::admin.layout.default')

@section('title', __('admin.user.actions.edit', ['name' => $user->first_name]))

@section('body')

    <div class="container-xl">

        <div class="card">

            <user-form
                :action="'{{ $action }}'"
                :data="{{ $user->toJson() }}"
                :activation="!!'{{ $activation }}'"
                inline-template>

                <form class="form-horizontal form-edit" method="post" @submit.prevent="onSubmit" :action="action">

                    <div class="card-header">
                        <i class="fa fa-pencil"></i> {{ __('admin.user.actions.edit', ['name' => $user->first_name]) }}
                    </div>

                    <div class="card-body">

                        @include('admin.user.components.form-elements')

                    </div>

                    <div class="card-footer">
	                    <button type="submit" class="btn btn-primary" :disabled="submiting">
		                    <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            {{ __('brackets/admin-ui::admin.btn.save') }}
	                    </button>
                    </div>

                </form>

            </user-form>

        </div>

    </div>

@endsection
