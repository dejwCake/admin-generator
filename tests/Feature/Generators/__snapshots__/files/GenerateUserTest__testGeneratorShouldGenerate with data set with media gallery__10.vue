<template>
    <form class="form-horizontal" method="post" @submit.prevent="onSubmit" :action="action" novalidate>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <i class="fa" :class="data && Object.keys(data).length > 0 ? 'fa-pencil' : 'fa-plus'"></i>
                        {{ translations.form_title }}
                    </div>
                    <div class="card-body">
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

                        <FormDatePicker
                            v-model="form.email_verified_at"
                            name="email_verified_at"
                            :label="translations.columns.email_verified_at"
                            :error="errors.email_verified_at"
                            :config="datetimePickerConfig"
                            :placeholder="translations.select_date_and_time"
                        />

                        <FormPasswordConfirm
                            v-model:password="form.password"
                            v-model:passwordConfirmation="form.password_confirmation"
                            :passwordError="errors.password"
                            :confirmationError="errors.password_confirmation"
                            :translations="{
                                password: translations.columns.password,
                                password_repeat: translations.columns.password_repeat
                            }"
                        />

                        <FormMultiSelect
                            v-model="form.roles"
                            name="roles"
                            :label="translations.relations.roles"
                            :error="errors.roles"
                            :options="roleOptions"
                            trackBy="id"
                            optionLabel="name"
                            :placeholder="translations.select_options"
                        />

                        <FormSubmit :submitting="submitting" :label="translations.save" />
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-lg-12 col-xl-5 col-xxl-4">
                <div class="card mt-2">
                    <div class="card-header">
                        <i class="fa fa-check"></i> {{ translations.gallery }}
                    </div>
                    <div class="card-body">
                        <MediaUpload
                            :ref="media.gallery.collection + '_uploader'"
                            :label="media.gallery.label"
                            :collection="media.gallery.collection"
                            :url="media.gallery.url"
                            :max-number-of-files="media.gallery.maxNumberOfFiles"
                            :max-file-size-in-mb="media.gallery.maxFileSizeInMb"
                            :accepted-file-types="media.gallery.acceptedFileTypes"
                            :uploaded-media="media.gallery.uploadedMedia"
                            :is-private="media.gallery.isPrivate ?? false"
                        />
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script setup>
import {useAppForm} from '../composables/useAppForm.js';
import FormInput from '@craftable/components/form/FormInput.vue';
import FormEmail from '@craftable/components/form/FormEmail.vue';
import FormDatePicker from '@craftable/components/form/FormDatePicker.vue';
import FormMultiSelect from '@craftable/components/form/FormMultiSelect.vue';
import FormPasswordConfirm from '@craftable/components/form/FormPasswordConfirm.vue';
import FormSubmit from '@craftable/components/form/FormSubmit.vue';
import MediaUpload from '@craftable/components/form/MediaUpload.vue';

const props = defineProps({
    action: {type: String, required: true},
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    roleOptions: {type: Array, default: () => []},
    media: {type: Object, default: () => ({gallery: {}})},
    responsiveBreakpoint: {type: Number, default: 850},
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
        name: 'required',
        email: 'required|email',
    },
});

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
        name: '',
        email: '',
        email_verified_at: '',
        password: '',
        password_confirmation: '',
        roles: [],
    };
}

mediaCollections.value = ['gallery'];
</script>
