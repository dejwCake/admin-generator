{{'@'}}extends('brackets/admin-ui::admin.layout.default')

{{'@'}}section('title', trans('admin.{{ $modelLangFormat }}.actions.edit_password'))

{{'@'}}section('body')

    <div class="container-xl">

        <{{ $modelJSName }}-form
            :action="'{{'{{'}} $action }}'"
            :data="{{'{{'}} ${{ $modelVariableName }}->toJson() }}"
            :translations="{{'{{'}} json_encode([
                'form_title' => trans('admin.{{ $modelLangFormat }}.actions.edit_password'),
                'columns' => [
                    'password' => trans('admin.{{ $modelLangFormat }}.columns.password'),
                    'password_repeat' => trans('admin.{{ $modelLangFormat }}.columns.password_repeat'),
                ],
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
        ></{{ $modelJSName }}-form>

    </div>

{{'@'}}endsection
