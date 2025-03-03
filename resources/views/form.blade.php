@if($hasTranslatable)
<div class="row form-inline" style="padding-bottom: 10px;" v-cloak>
    <div :class="{'col-xl-10 col-md-11 text-right': !isFormLocalized, 'col text-center': isFormLocalized, 'hidden': onSmallScreen }">
        <small>@{{ trans('brackets/admin-ui::admin.forms.currently_editing_translation') }}<span v-if="!isFormLocalized && otherLocales.length > 1"> @{{ trans('brackets/admin-ui::admin.forms.more_can_be_managed') }}</span><span v-if="!isFormLocalized"> | <a href="#" @click.prevent="showLocalization">@{{ trans('brackets/admin-ui::admin.forms.manage_translations') }}</a></span></small>
        <i class="localization-error" v-if="!isFormLocalized && showLocalizedValidationError"></i>
    </div>

    <div class="col text-center" :class="{'language-mobile': onSmallScreen, 'has-error': !isFormLocalized && showLocalizedValidationError}" v-if="isFormLocalized || onSmallScreen" v-cloak>
        <small>@{{ trans('brackets/admin-ui::admin.forms.choose_translation_to_edit') }}
            <select class="form-control" v-model="currentLocale">
                <option :value="defaultLocale" v-if="onSmallScreen">{{'@{{'}}defaultLocale.toUpperCase()}}</option>
                <option v-for="locale in otherLocales" :value="locale">{{'@{{'}}locale.toUpperCase()}}</option>
            </select>
            <i class="localization-error" v-if="isFormLocalized && showLocalizedValidationError"></i>
            <span>|</span>
            <a href="#" @click.prevent="hideLocalization">@{{ trans('brackets/admin-ui::admin.forms.hide') }}</a>
        </small>
    </div>
</div>
@endif
{{-- TODO extract to the exceptional array  --}}
@foreach($columns as $col)
@if(!in_array($col['name'], ['created_by_admin_user_id','updated_by_admin_user_id'], true))
@if($col['name'] === 'password')
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <label for="{{ $col['name'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
    <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <input type="password" v-model="form.{{ $col['name'] }}" v-validate="'{{ implode('|', collect($col['frontendRules'])->reject(function($rule) use ($col) { return $rule === 'confirmed:'.$col['name'];})->toArray()) }}'" @input="validate($event)" class="form-control" :class="{'form-control-danger': errors.has('{{ $col['name'] }}'), 'form-control-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid}" id="{{ $col['name'] }}" name="{{ $col['name'] }}" placeholder="{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}" ref="{{ $col['name'] }}">
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>

<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}_confirmation'), 'has-success': fields.{{ $col['name'] }}_confirmation && fields.{{ $col['name'] }}_confirmation.valid }">
    <label for="{{ $col['name'] }}_confirmation" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}_repeat') }}</label>
    <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <input type="password" v-model="form.{{ $col['name'] }}_confirmation" v-validate="'{{ implode('|', $col['frontendRules']) }}'" @input="validate($event)" class="form-control" :class="{'form-control-danger': errors.has('{{ $col['name'] }}_confirmation'), 'form-control-success': fields.{{ $col['name'] }}_confirmation && fields.{{ $col['name'] }}_confirmation.valid}" id="{{ $col['name'] }}_confirmation" name="{{ $col['name'] }}_confirmation" placeholder="{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}" data-vv-as="{{ $col['name'] }}">
        <div v-if="errors.has('{{ $col['name'] }}_confirmation')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}_confirmation') }}</div>
    </div>
</div>
@elseif($col['majorType'] === 'date' && !in_array($col['name'], ['published_at'], true))
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <label for="{{ $col['name'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
    <div :class="isFormLocalized ? 'col-md-4' : 'col-sm-8'">
        <div class="input-group input-group--custom">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <datetime v-model="form.{{ $col['name'] }}" :config="datePickerConfig" v-validate="'{{ implode('|', $col['frontendRules']) }}'" class="flatpickr" :class="{'form-control-danger': errors.has('{{ $col['name'] }}'), 'form-control-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid}" id="{{ $col['name'] }}" name="{{ $col['name'] }}" placeholder="@{{ trans('brackets/admin-ui::admin.forms.select_a_date') }}"></datetime>
        </div>
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>
@elseif($col['majorType'] === 'time')
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <label for="{{ $col['name'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
    <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <div class="input-group input-group--custom">
            <div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
            <datetime v-model="form.{{ $col['name'] }}" :config="timePickerConfig" v-validate="'{{ implode('|', $col['frontendRules']) }}'" class="flatpickr" :class="{'form-control-danger': errors.has('{{ $col['name'] }}'), 'form-control-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid}" id="{{ $col['name'] }}" name="{{ $col['name'] }}" placeholder="@{{ trans('brackets/admin-ui::admin.forms.select_a_time') }}"></datetime>
        </div>
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>

@elseif($col['majorType'] === 'datetime' && !in_array($col['name'], ['published_at'], true))
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <label for="{{ $col['name'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
    <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <div class="input-group input-group--custom">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <datetime v-model="form.{{ $col['name'] }}" :config="datetimePickerConfig" v-validate="'{{ implode('|', $col['frontendRules']) }}'" class="flatpickr" :class="{'form-control-danger': errors.has('{{ $col['name'] }}'), 'form-control-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid}" id="{{ $col['name'] }}" name="{{ $col['name'] }}" placeholder="@{{ trans('brackets/admin-ui::admin.forms.select_date_and_time') }}"></datetime>
        </div>
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>
@elseif($col['majorType'] === 'text' && in_array($col['name'], $wysiwygTextColumnNames, true))
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <label for="{{ $col['name'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
        <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <div>
            <wysiwyg v-model="form.{{ $col['name'] }}" v-validate="'{{ implode('|', $col['frontendRules']) }}'" id="{{ $col['name'] }}" name="{{ $col['name'] }}" :config="mediaWysiwygConfig"></wysiwyg>
        </div>
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>
@elseif($col['majorType'] === 'text')
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <label for="{{ $col['name'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
    <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <div>
            <textarea class="form-control" v-model="form.{{ $col['name'] }}" v-validate="'{{ implode('|', $col['frontendRules']) }}'" id="{{ $col['name'] }}" name="{{ $col['name'] }}"></textarea>
        </div>
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>
@elseif($col['majorType'] === 'bool')
<div class="form-check row" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <div class="ml-md-auto" :class="isFormLocalized ? 'col-md-8' : 'col-md-10'">
        <input class="form-check-input" id="{{ $col['name'] }}" type="checkbox" v-model="form.{{ $col['name'] }}" v-validate="'{{ implode('|', $col['frontendRules']) }}'" data-vv-name="{{ $col['name'] }}"  name="{{ $col['name'] }}_fake_element">
        <label class="form-check-label" for="{{ $col['name'] }}">
            {{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}
        </label>
        <input type="hidden" name="{{ $col['name'] }}" :value="form.{{ $col['name'] }}">
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>
@elseif($col['majorType'] === 'json')
<div class="row">
    {{'@'}}foreach($locales as $locale)
        <div class="col-md" v-show="shouldShowLangGroup('@{{ $locale }}')" v-cloak>
            <div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}_@{{ $locale }}'), 'has-success': fields.{{ $col['name'] }}_@{{ $locale }} && fields.{{ $col['name'] }}_@{{ $locale }}.valid }">
                <label for="{{ $col['name'] }}_@{{ $locale }}" class="col-md-2 col-form-label text-md-right">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
                <div class="col-md-9" :class="{'col-xl-8': !isFormLocalized }">
@if(in_array($col['name'], $wysiwygTextColumnNames, true))
                    <div>
                        <wysiwyg v-model="form.{{ $col['name'] }}.@{{ $locale }}" v-validate="'{!! implode('|', $col['frontendRules']) !!}'" id="{{ $col['name'] }}_@{{ $locale }}" name="{{ $col['name'] }}_@{{ $locale }}" :config="mediaWysiwygConfig"></wysiwyg>
                    </div>
@else
                    <input type="text" v-model="form.{{ $col['name'] }}.@{{ $locale }}" v-validate="'{!! implode('|', $col['frontendRules']) !!}'" @input="validate($event)" class="form-control" :class="{'form-control-danger': errors.has('{{ $col['name'] }}_@{{ $locale }}'), 'form-control-success': fields.{{ $col['name'] }}_@{{ $locale }} && fields.{{ $col['name'] }}_@{{ $locale }}.valid }" id="{{ $col['name'] }}_@{{ $locale }}" name="{{ $col['name'] }}_@{{ $locale }}" placeholder="{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}">
@endif
                    <div v-if="errors.has('{{ $col['name'] }}_@{{ $locale }}')" class="form-control-feedback form-text" v-cloak>@{{'{{'}} errors.first('{{ $col['name'] }}_@{{ $locale }}') }}</div>
                </div>
            </div>
        </div>
    {{'@'}}endforeach
</div>
@elseif(!in_array($col['name'], ['published_at'], true))
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $col['name'] }}'), 'has-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid }">
    <label for="{{ $col['name'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}</label>
        <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <input type="text" v-model="form.{{ $col['name'] }}" v-validate="'{{ implode('|', $col['frontendRules']) }}'" @input="validate($event)" class="form-control" :class="{'form-control-danger': errors.has('{{ $col['name'] }}'), 'form-control-success': fields.{{ $col['name'] }} && fields.{{ $col['name'] }}.valid}" id="{{ $col['name'] }}" name="{{ $col['name'] }}" placeholder="{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ $col['name'] }}') }}">
        <div v-if="errors.has('{{ $col['name'] }}')" class="form-control-feedback form-text" v-cloak>{{'@{{'}} errors.first('{{ $col['name'] }}') }}</div>
    </div>
</div>
@endif
@endif

@endforeach

@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)
@foreach($relations['belongsToMany'] as $belongsToMany)
<div class="form-group row align-items-center" :class="{'has-danger': errors.has('{{ $belongsToMany['related_table'] }}'), 'has-success': fields.{{ $belongsToMany['related_table'] }} && fields.{{ $belongsToMany['related_table'] }}.valid }">
    <label for="{{ $belongsToMany['related_table'] }}" class="col-form-label text-md-right" :class="isFormLocalized ? 'col-md-4' : 'col-md-2'">{{'{{'}} trans('admin.{{ $modelLangFormat }}.columns.{{ lcfirst($belongsToMany['related_model_name_plural']) }}') }}</label>
    <div :class="isFormLocalized ? 'col-md-4' : 'col-md-9 col-xl-8'">
        <multiselect v-model="form.{{ $belongsToMany['related_table'] }}" placeholder="@{{ trans('brackets/admin-ui::admin.forms.select_options') }}" label="name" track-by="id" :options="{{'{{'}} ${{ $belongsToMany['related_table'] }}->toJson() }}" :multiple="true" open-direction="bottom"></multiselect>
        <div v-if="errors.has('{{ $belongsToMany['related_table'] }}')" class="form-control-feedback form-text" v-cloak>@@{{ errors.first('@php echo $belongsToMany['related_table']; @endphp') }}</div>
    </div>
</div>
@endforeach
@endif
