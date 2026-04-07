@php
    use Brackets\AdminGenerator\Dtos\Columns\Column;
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Illuminate\Support\Collection;
    assert($profileColumns instanceof ColumnCollection);
@endphp
<template>
    <form class="form-horizontal" method="post" @@submit.prevent="onSubmit" :action="action" novalidate>
        <div class="card">
            <div class="card-header">
                <i class="fa fa-pencil"></i> {{ '{{' }} translations.form_title }}
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="avatar-upload">
                            <MediaUpload
                                :ref="`${media.collection}_uploader`"
                                :collection="media.collection"
                                :url="media.url"
                                :max-number-of-files="media.maxNumberOfFiles"
                                :max-file-size-in-mb="media.maxFileSizeInMb"
                                :accepted-file-types="media.acceptedFileTypes"
                                :uploaded-media="media.uploadedMedia"
                            />
                        </div>
                    </div>
                    <div class="col-md-8">
@foreach($profileColumns as $column)
@if($column->name === 'email')
                        <FormEmail
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@elseif($column->name === 'language')
                        <FormSelect
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :options="languageOptions"
                            :placeholder="translations.select_an_option"
                        />

@elseif($column->majorType === 'bool')
                        <FormCheckbox
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@else
                        <FormInput
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@endif
@endforeach
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <FormSubmit :submitting="submitting" :label="translations.save" />
            </div>
        </div>
    </form>
</template>

@php
    $imports = new Collection([
        "{useAppForm} from '../composables/useAppForm.js'",
        "{mediaCollectionProp} from '@craftable/utils/mediaProps.js'",
        "MediaUpload from '@craftable/components/form/MediaUpload.vue'",
    ]);
    if ($profileColumns->hasFormInput()) {
        $imports->push("FormInput from '@craftable/components/form/FormInput.vue'");
    }
    if ($profileColumns->hasByName('email')) {
        $imports->push("FormEmail from '@craftable/components/form/FormEmail.vue'");
    }
    if ($profileColumns->hasByName('language')) {
        $imports->push("FormSelect from '@craftable/components/form/FormSelect.vue'");
    }
    if ($profileColumns->hasByMajorType('bool')) {
        $imports->push("FormCheckbox from '@craftable/components/form/FormCheckbox.vue'");
    }
    $imports->push("FormSubmit from '@craftable/components/form/FormSubmit.vue'");
@endphp
<script setup>
@foreach($imports as $import)
import {!! $import !!};
@endforeach

const props = defineProps({
    action: { type: String, required: true },
    data: { type: Object, default: () => ({}) },
    translations: { type: Object, default: () => ({}) },
@if($profileColumns->hasByName('language'))
    languageOptions: { type: Array, default: () => [] },
@endif
    media: mediaCollectionProp,
    locales: { type: Array, default: () => [] },
    defaultLocale: { type: String, default: '' },
    sendEmptyLocales: { type: Boolean, default: true },
    responsiveBreakpoint: { type: Number, default: 850 },
});

const {
    form, wysiwygMedia, mediaCollections, isFormLocalized, currentLocale,
    submitting, onSmallScreen, errors, datePickerConfig, timePickerConfig,
    datetimePickerConfig, locales, defaultLocale, otherLocales,
    showLocalizedValidationError, getPostData, onSubmit, onSuccess, onFail,
    getLocalizedFormDefaults, showLocalization, hideLocalization,
    shouldShowLangGroup,
} = useAppForm(props, {
    validationSchema: {
@if($profileColumns->hasByName('email'))
        email: 'required|email',
@endif
@if($profileColumns->hasByName('language'))
        language: 'required',
@endif
    },
});

mediaCollections.value = ['avatar'];

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
@foreach($profileColumns as $column)
@if($column->majorType === 'json')
        {{ $column->name }}: getLocalizedFormDefaults(),
@elseif($column->majorType === 'bool')
        {{ $column->name }}: false,
@else
        {{ $column->name }}: '',
@endif
@endforeach
    };
}
</script>
