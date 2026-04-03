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
@foreach($profileColumns as $col)
@if($col['name'] === 'email')
                        <FormEmail v-model="form.{{ $col['name'] }}" name="{{ $col['name'] }}"
                            :label="translations.columns.{{ $col['name'] }}" :error="errors.{{ $col['name'] }}" />

@elseif($col['name'] === 'language')
                        <FormSelect v-model="form.{{ $col['name'] }}" name="{{ $col['name'] }}"
                            :label="translations.columns.{{ $col['name'] }}" :error="errors.{{ $col['name'] }}"
                            :options="languageOptions" :placeholder="translations.select_an_option" />

@elseif($col['majorType'] === 'bool')
                        <FormCheckbox v-model="form.{{ $col['name'] }}" name="{{ $col['name'] }}"
                            :label="translations.columns.{{ $col['name'] }}" :error="errors.{{ $col['name'] }}" />

@else
                        <FormInput v-model="form.{{ $col['name'] }}" name="{{ $col['name'] }}"
                            :label="translations.columns.{{ $col['name'] }}" :error="errors.{{ $col['name'] }}" />

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

<script setup>
import { useAppForm } from '../composables/useAppForm.js';
import { mediaCollectionProp } from '@@craftable/utils/mediaProps.js';
import MediaUpload from '@@craftable/components/form/MediaUpload.vue';
@if($profileColumns->contains(static fn (array $col): bool => !in_array($col['name'], ['email', 'language'], true) && $col['majorType'] !== 'bool'))
import FormInput from '@@craftable/components/form/FormInput.vue';
@endif
@if($hasEmail)
import FormEmail from '@@craftable/components/form/FormEmail.vue';
@endif
@if($hasLanguage)
import FormSelect from '@@craftable/components/form/FormSelect.vue';
@endif
@if($profileColumns->contains(static fn (array $col): bool => $col['majorType'] === 'bool'))
import FormCheckbox from '@@craftable/components/form/FormCheckbox.vue';
@endif
import FormSubmit from '@@craftable/components/form/FormSubmit.vue';

const props = defineProps({
    action: { type: String, required: true },
    data: { type: Object, default: () => ({}) },
    translations: { type: Object, default: () => ({}) },
@if($hasLanguage)
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
@if($hasEmail)
        email: 'required|email',
@endif
@if($hasLanguage)
        language: 'required',
@endif
    },
});

mediaCollections.value = ['avatar'];

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
@foreach($profileColumns as $col)
@if($col['majorType'] === 'json')
        {{ $col['name'] }}: getLocalizedFormDefaults(),
@elseif($col['majorType'] === 'bool')
        {{ $col['name'] }}: false,
@else
        {{ $col['name'] }}: '',
@endif
@endforeach
    };
}
</script>
