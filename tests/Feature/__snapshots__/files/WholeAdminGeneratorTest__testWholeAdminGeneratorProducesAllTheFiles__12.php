@extends('brackets/admin-ui::admin.layout.default')

@section('title', __('admin.category.actions.edit', ['name' => $category->title]))

@section('body')

    <div class="container-xl">
        <div class="card">

            <category-form
                :action="'{{ $action }}'"
                :data="{{ $category->toJson() }}"
                v-cloak
                inline-template>

                <form class="form-horizontal form-edit" method="post" @submit.prevent="onSubmit" :action="action" novalidate>


                    <div class="card-header">
                        <i class="fa fa-pencil"></i> {{ __('admin.category.actions.edit', ['name' => $category->title]) }}
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