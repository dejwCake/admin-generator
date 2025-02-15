{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', __(('admin.{{ $modelLangFormat }}.actions.edit_profile'))

{{'@'}}section('body')

    <div class="container-xl">

        <div class="card">

            <{{ $modelJSName }}-form
                :action="'{{'{{'}} $action }}'"
                :data="{{'{{'}} ${{ $modelVariableName }}->toJson() }}"
@if($hasTranslatable)
                :locales="@{{ json_encode($locales) }}"
                :send-empty-locales="false"
@endif
                inline-template>

                <form class="form-horizontal form-edit" method="post" {{'@'}}submit.prevent="onSubmit" :action="action">

                    <div class="card-header">
                        <i class="fa fa-pencil"></i> {{'{{'}} __(('admin.{{ $modelLangFormat }}.actions.edit_profile') }}
                    </div>

                    <div class="card-body">

@php
    $columns = $columns->reject(function($column) {
        return in_array($column['name'], ['password', 'activated', 'forbidden']);
    });
@endphp
                        @include('brackets/admin-generator::templates.profile.form', ['columns' => $columns])

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" :disabled="submiting">
                            <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            @{{ __(('brackets/admin-ui::admin.btn.save') }}
                        </button>
                    </div>

                </form>

            </{{ $modelJSName }}-form>

        </div>

    </div>

{{'@'}}endsection