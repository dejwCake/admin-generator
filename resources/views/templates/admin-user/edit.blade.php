{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', __('admin.{{ $modelLangFormat }}.actions.edit', ['name' => ${{ $modelVariableName }}->{{$modelTitle}}]))

{{'@'}}section('body')

    <div class="container-xl">

        <div class="card">

@if($hasTranslatable)
            <{{ $modelJSName }}-form
                :action="'{{'{{'}} $action }}'"
                :data="{{'{{'}} ${{ $modelVariableName }}->toJsonAllLocales() }}"
                :activation="!!'@{{ $activation }}'"
                :locales="@{{ json_encode($locales) }}"
                :send-empty-locales="false"
                inline-template>
@else
            <{{ $modelJSName }}-form
                :action="'{{'{{'}} $action }}'"
                :data="{{'{{'}} ${{ $modelVariableName }}->toJson() }}"
                :activation="!!'@{{ $activation }}'"
                inline-template>
@endif

                <form class="form-horizontal form-edit" method="post" {{'@'}}submit.prevent="onSubmit" :action="action">

                    <div class="card-header">
                        <i class="fa fa-pencil"></i> {{'{{'}} __('admin.{{ $modelLangFormat }}.actions.edit', ['name' => ${{ $modelVariableName }}->{{$modelTitle}}]) }}
                    </div>

                    <div class="card-body">

                        {{'@'}}include('admin.{{ $modelDotNotation }}.components.form-elements')

                    </div>

                    <div class="card-footer">
	                    <button type="submit" class="btn btn-primary" :disabled="submiting">
		                    <i class="fa" :class="submiting ? 'fa-spinner' : 'fa-download'"></i>
                            @{{ __('brackets/admin-ui::admin.btn.save') }}
	                    </button>
                    </div>

                </form>

            </{{ $modelJSName }}-form>

        </div>

    </div>

{{'@'}}endsection
