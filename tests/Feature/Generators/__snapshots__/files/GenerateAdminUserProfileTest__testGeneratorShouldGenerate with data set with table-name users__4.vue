<template>
    <form class="form-horizontal" method="post" @submit.prevent="onSubmit" :action="action" novalidate>
        <div class="card">
            <div class="card-header">
                <i class="fa fa-pencil"></i> {{ translations.form_title }}
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
                        <FormInput
                            v-model="form.name"
                            name="name"
                            :label="translations.columns.name"
                            :error="errors.name"
                        />

                        <FormEmail
                            v-model="form.email"
                            name="email"
                            :label="translations.columns.email"
                            :error="errors.email"
                        />

                        <FormInput
                            v-model="form.email_verified_at"
                            name="email_verified_at"
                            :label="translations.columns.email_verified_at"
                            :error="errors.email_verified_at"
                        />

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
import {useAppForm} from '../composables/useAppForm.js';
import {mediaCollectionProp} from '@craftable/utils/mediaProps.js';
import MediaUpload from '@craftable/components/form/MediaUpload.vue';
import FormInput from '@craftable/components/form/FormInput.vue';
import FormEmail from '@craftable/components/form/FormEmail.vue';
import FormSubmit from '@craftable/components/form/FormSubmit.vue';

const props = defineProps({
    action: { type: String, required: true },
    data: { type: Object, default: () => ({}) },
    translations: { type: Object, default: () => ({}) },
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
        email: 'required|email',
    },
});

mediaCollections.value = ['avatar'];

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
        name: '',
        email: '',
        email_verified_at: '',
    };
}
</script>
