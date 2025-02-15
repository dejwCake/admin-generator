@extends('brackets/admin-ui::admin.layout.default')

@section('title', __('admin.billing_my-article.actions.edit', ['name' => $myArticle->title]))

@section('body')

    <div class="container-xl">
        <div class="card">

            <billing-my-article-form
                :action="'{{ $action }}'"
                :data="{{ $myArticle->toJson() }}"
                v-cloak
                inline-template>

                <form class="form-horizontal form-edit" method="post" @submit.prevent="onSubmit" :action="action" novalidate>


                    <div class="card-header">
                        <i class="fa fa-pencil"></i> {{ __('admin.billing_my-article.actions.edit', ['name' => $myArticle->title]) }}
                    </div>

                    <div class="card-body">
                        @include('admin.billing.my-article.components.form-elements')
                    </div>


                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" :disabled="submiting">
                            <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            {{ __('brackets/admin-ui::admin.btn.save') }}
                        </button>
                    </div>

                </form>

            </billing-my-article-form>

        </div>

    </div>

@endsection