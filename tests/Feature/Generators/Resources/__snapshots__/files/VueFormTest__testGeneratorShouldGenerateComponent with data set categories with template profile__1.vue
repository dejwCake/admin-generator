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
                            v-model="form.user_id"
                            name="user_id"
                            :label="translations.columns.user_id"
                            :error="errors.user_id"
                        />

                        <FormInput
                            v-model="form.title"
                            name="title"
                            :label="translations.columns.title"
                            :error="errors.title"
                        />

                        <FormInput
                            v-model="form.name"
                            name="name"
                            :label="translations.columns.name"
                            :error="errors.name"
                        />

                        <FormInput
                            v-model="form.first_name"
                            name="first_name"
                            :label="translations.columns.first_name"
                            :error="errors.first_name"
                        />

                        <FormInput
                            v-model="form.last_name"
                            name="last_name"
                            :label="translations.columns.last_name"
                            :error="errors.last_name"
                        />

                        <FormInput
                            v-model="form.subject"
                            name="subject"
                            :label="translations.columns.subject"
                            :error="errors.subject"
                        />

                        <FormEmail
                            v-model="form.email"
                            name="email"
                            :label="translations.columns.email"
                            :error="errors.email"
                        />

                        <FormSelect
                            v-model="form.language"
                            name="language"
                            :label="translations.columns.language"
                            :error="errors.language"
                            :options="languageOptions"
                            :placeholder="translations.select_an_option"
                        />

                        <FormInput
                            v-model="form.slug"
                            name="slug"
                            :label="translations.columns.slug"
                            :error="errors.slug"
                        />

                        <FormInput
                            v-model="form.perex"
                            name="perex"
                            :label="translations.columns.perex"
                            :error="errors.perex"
                        />

                        <FormInput
                            v-model="form.long_text"
                            name="long_text"
                            :label="translations.columns.long_text"
                            :error="errors.long_text"
                        />

                        <FormInput
                            v-model="form.date_start"
                            name="date_start"
                            :label="translations.columns.date_start"
                            :error="errors.date_start"
                        />

                        <FormInput
                            v-model="form.time_start"
                            name="time_start"
                            :label="translations.columns.time_start"
                            :error="errors.time_start"
                        />

                        <FormInput
                            v-model="form.date_time_end"
                            name="date_time_end"
                            :label="translations.columns.date_time_end"
                            :error="errors.date_time_end"
                        />

                        <FormInput
                            v-model="form.released_at"
                            name="released_at"
                            :label="translations.columns.released_at"
                            :error="errors.released_at"
                        />

                        <FormInput
                            v-model="form.text"
                            name="text"
                            :label="translations.columns.text"
                            :error="errors.text"
                        />

                        <FormInput
                            v-model="form.description"
                            name="description"
                            :label="translations.columns.description"
                            :error="errors.description"
                        />

                        <FormCheckbox
                            v-model="form.enabled"
                            name="enabled"
                            :label="translations.columns.enabled"
                            :error="errors.enabled"
                        />

                        <FormCheckbox
                            v-model="form.send"
                            name="send"
                            :label="translations.columns.send"
                            :error="errors.send"
                        />

                        <FormInput
                            v-model="form.price"
                            name="price"
                            :label="translations.columns.price"
                            :error="errors.price"
                        />

                        <FormInput
                            v-model="form.rating"
                            name="rating"
                            :label="translations.columns.rating"
                            :error="errors.rating"
                        />

                        <FormInput
                            v-model="form.views"
                            name="views"
                            :label="translations.columns.views"
                            :error="errors.views"
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
import FormSelect from '@craftable/components/form/FormSelect.vue';
import FormCheckbox from '@craftable/components/form/FormCheckbox.vue';
import FormSubmit from '@craftable/components/form/FormSubmit.vue';

const props = defineProps({
    action: { type: String, required: true },
    data: { type: Object, default: () => ({}) },
    translations: { type: Object, default: () => ({}) },
    languageOptions: { type: Array, default: () => [] },
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
        language: 'required',
    },
});

mediaCollections.value = ['avatar'];

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
        user_id: '',
        title: '',
        name: '',
        first_name: '',
        last_name: '',
        subject: '',
        email: '',
        language: '',
        slug: '',
        perex: '',
        long_text: getLocalizedFormDefaults(),
        date_start: '',
        time_start: '',
        date_time_end: '',
        released_at: '',
        text: getLocalizedFormDefaults(),
        description: getLocalizedFormDefaults(),
        enabled: false,
        send: false,
        price: '',
        rating: '',
        views: '',
    };
}
</script>
